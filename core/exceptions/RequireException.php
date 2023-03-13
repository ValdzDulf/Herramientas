<?php

namespace core\exceptions;

use Exception;

/**
 * Exception thrown if there are errors when trying to access a file.
 *
 * @package   core\exceptions
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class RequireException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
