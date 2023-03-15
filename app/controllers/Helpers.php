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
     * @param bool        $isZeroAllowed Flag indicating if zero is allowed.
     * @param string|null $string        String to validate.
     */
    public static function isEmpty($string, $isZeroAllowed = false)
    {
        if ((!$isZeroAllowed && empty($string)) || ($isZeroAllowed && $string !== '0' && empty($string))) {
            throw new InputDataException('This data is required');
        }
    }
}
