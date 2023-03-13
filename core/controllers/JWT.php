<?php

namespace core\controllers;

use core\exceptions\AuthException;

/**
 * Allows working with JSON Web token locally
 *
 * @package   core\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 - Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class JWT
{
    /**
     * Converts an array|string to a Base64-encoded JSON string.
     *
     * @param  string|array  $data        Array or string to be encoded.
     * @param  boolean       $encodeJSON  Flag indicating whether the received element should be converted to
     *                                    JSON.
     *
     * @return string string in base 64.
     */
    private static function base64Encode($data, $encodeJSON = true)
    {
        # When necessary, convert to JSON.
        $encode = ($encodeJSON) ? json_encode($data) : $data;

        return urlencode(base64_encode($encode));
    }

    /**
     * Fetches the contents of a base 64-encoded string.
     *
     * @param  string   $data        String to be decoded.
     * @param  boolean  $decodeJSON  Flag indicating whether the received element should be converted from
     *                               JSON to object.
     *
     * @return string|object String or decoded object.
     */
    private static function base64Decode($data, $decodeJSON = true)
    {
        $decode = base64_decode(urldecode($data));

        return ($decodeJSON) ? json_decode($decode) : $decode;
    }

    /**
     * Fetches the payload of JWT.
     *
     * @throws AuthException Will throw the exception if JWT is malformed or any elements doesn't exist.
     *
     * @param  string   $jwt               JWT structure.
     * @param  string   $secretPassword    Password to generate signature.
     * @param  boolean  $validateValidity  Flag indicating whether to validate validity.
     *
     * @return object Statements about the entity.
     */
    public static function getPayload($jwt, $secretPassword = null, $validateValidity = false)
    {
        $jwtElements = explode('.', $jwt);

        if (count($jwtElements) != 3) {
            throw new AuthException('JWT malformed.');
        }

        list($base64Header, $base64Payload, $base64Signature) = $jwtElements;

        $header = self::base64Decode($base64Header);
        $payload = self::base64Decode($base64Payload);

        if (!isset($header)) {
            throw new AuthException("JWT header doesn't exist");
        }

        if (!isset($payload)) {
            throw new AuthException("JWT payload doesn't exist");
        }

        if (isset($secretPassword)) {
            if (empty($header->alg)) {
                throw new AuthException("JWT header malformed");
            }

            if (!self::validateSignature(
                    $base64Signature, $base64Header, $base64Payload, $secretPassword, $header->alg)) {
                throw new AuthException("JWT key is invalid");
            }
        }

        if ($validateValidity) {
            $now = time();

            $exp = $payload->exp;

            if ($now > $exp) {
                throw new AuthException('Session expired');
            }
        }

        return $payload;
    }

    /**
     * Generates a JSON Web Token.
     *
     * @throws AuthException Will throw the exception if no token checking mechanism exists.
     *
     * @param  array   $payload         Array containing the token body.
     * @param  string  $secretPassword  Password to generate signature.
     * @param  string  $alg             Algorithm to be used to generate the signature.
     *
     * @return string String representing the JWT
     */
    public static function encode($payload, $secretPassword, $alg = 'HS256')
    {
        # Generates the JWT header.
        $header = self::base64Encode(['typ' => 'JWT', 'alg' => $alg]);

        # Generates the JWT payload.
        $payload = self::base64Encode($payload);

        # Generates the signature for the generated string.
        $signature = self::generateSignature($header, $payload, $secretPassword, $alg);

        # Generates an array of the elements that make up the JWT, already encoded in Base64.
        $jwtElements = [
                $header,
                $payload,
                self::base64Encode($signature, false)
        ];

        return implode('.', $jwtElements);
    }

    /**
     * Generates the signature for the header and body of the JWT.
     *
     * @throws AuthException Will throw the exception if no token checking mechanism exists.
     *
     * @param  string  $header          JWT header.
     * @param  string  $payload         JWT payload.
     * @param  string  $secretPassword  Password to generate signature.
     * @param  string  $alg             Verification algorithm.
     *
     * @return string Signature of the string received.
     */
    private static function generateSignature($header, $payload, $secretPassword, $alg)
    {
        switch ($alg) {
            case 'HS256':
                return hash_hmac('sha256', $header . $payload, $secretPassword, true);
            default:
                throw new AuthException('Invalid credential verification form.');
        }
    }

    /**
     * Verifies that the signature received matches the signature generated.
     *
     * @throws AuthException Will throw the exception if no token checking mechanism exists.
     *
     * @param  string  $header          JWT header.
     * @param  string  $payload         Array containing the token body.
     * @param  string  $secretPassword  Password to generate signature.
     * @param  string  $alg             Algorithm to be used to generate the signature.
     * @param  string  $signature       Signature to be verified.
     *
     * @return bool
     */
    private static function validateSignature($signature, $header, $payload, $secretPassword, $alg)
    {
        switch ($alg) {
            case'HS256':
                $validation = self::base64Encode(
                        self::generateSignature($header, $payload, $secretPassword, $alg), false
                );

                return $signature === $validation;
            default:
                throw new AuthException('Invalid credential verification form.');
        }
    }

    /**
     * Allows to verify if a JWT is active.
     *
     * @throws AuthException Will throw the exception if JWT is malformed or any elements doesn't exist.
     *
     * @param  string   $jwt               JWT structure.
     * @param  string   $secretPassword    Password to generate signature.
     *
     * @return bool True if active, false if not active.
     */
    public static function verifyValidity($jwt, $secretPassword = null)
    {
        $jwtElements = explode('.', $jwt);

        if (count($jwtElements) != 3) {
            throw new AuthException('JWT malformed.');
        }

        list($base64Header, $base64Payload, $base64Signature) = $jwtElements;

        $header = self::base64Decode($base64Header);
        $payload = self::base64Decode($base64Payload);

        if (!isset($header)) {
            throw new AuthException("JWT header doesn't exist");
        }

        if (!isset($payload)) {
            throw new AuthException("JWT payload doesn't exist");
        }

        if (isset($secretPassword)) {
            if (empty($header->alg)) {
                throw new AuthException("JWT header malformed");
            }

            if (!self::validateSignature(
                    $base64Signature, $base64Header, $base64Payload, $secretPassword, $header->alg)) {
                throw new AuthException("JWT key is invalid");
            }
        }

        $now = time();

        $exp = $payload->exp;

        return ($now <= $exp);
    }
}
