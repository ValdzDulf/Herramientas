<?php

namespace app\controllers;

use core\exceptions\InputDataException;

/**
 * Defines auxiliary methods to provide extra functionality.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class Helpers
{
    /**
     * Create the folder or check if it exists.
     *
     * @param  string  $path  Path where the file will be stored.
     *
     * @return bool true if folder was created or exists, false if wasn't created or doesn't exist.
     */
    public static function createFolder($path)
    {
        return @mkdir($path, 0777, true) || @is_dir($path);
    }

    /**
     * Retrieve IP address.
     *
     * @return string
     */
    public static function getIP()
    {
        return empty($_SERVER['HTTP_X_FORWARDED_FOR']) ?
                $_SERVER['REMOTE_ADDR'] : $_SERVER['HTTP_X_FORWARDED_FOR'];
    }

    /**
     * Checks if the string is empty.
     *
     * @throws InputDataException Will throw the exception if the string is empty.
     *
     * @param  bool         $isZeroAllowed  Flag indicating if zero is allowed.
     * @param  string|null  $string         String to validate.
     */
    public static function isEmpty($string, $isZeroAllowed = false)
    {
        if ((!$isZeroAllowed && empty($string)) || ($isZeroAllowed && $string !== '0' && empty($string))) {
            throw new InputDataException('This data is required');
        }
    }

    /**
     * Checks if the email is in SERTEC's domain.
     *
     * @throws InputDataException Will throw the exception if the email is invalid.
     *
     * @param  string  $email  Email to validate.
     *
     * @return void
     */
    public static function isMailFromSertecDomain($email)
    {
        $regex = '/^[\w.-]+@sertec.com.mx$/';

        if (!preg_match($regex, $email)) {
            throw new InputDataException("Doesn't belong to the domain sertec.com.mx");
        }
    }

    /**
     * Checks if the phone extension is in the correct format.
     *
     * @throws InputDataException Will throw the exception if the email is invalid.
     *
     * @param  string  $phoneExtension  Extension to validate.
     *
     * @return void
     */
    public static function isValidExtension($phoneExtension)
    {
        $regex = '/^[0-9]{4}$/';

        if (!preg_match($regex, $phoneExtension)) {
            throw new InputDataException("Doesn't comply with the requested format (four digits)");
        }
    }
}
