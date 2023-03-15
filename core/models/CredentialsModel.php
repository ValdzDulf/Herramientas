<?php

namespace core\models;

/**
 * Describes the business rules for handling access credentials.
 *
 * @package   core\models
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class CredentialsModel
{
    /**
     * @var string $username User's key to access the application.
     */
    public $username;

    /**
     * @var string $password User's password to access the application.
     */
    public $password;
}
