<?php

namespace core\exceptions;

use Exception;

/**
 * Exception thrown if the interface doesn't exist or is not available.
 *
 * @package   core\exceptions
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class InterfaceAvailableException extends Exception
{
    public function __construct($message = '', $code = 0)
    {
        parent::__construct($message, $code);
    }
}
