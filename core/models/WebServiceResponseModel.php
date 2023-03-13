<?php

namespace core\models;

/**
 * Describes the business rules for the handling of responses to a request.
 *
 * @package   core\models
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class WebServiceResponseModel
{
    /**
     * @var integer $statusCode HTTP status code.
     */
    public $statusCode;

    /**
     * @var object $message Informative message.
     */
    public $message;

    /**
     * @var string Access token returning a request
     */
    public $accessToken;

    /**
     * @var object $data Data to be returned.
     */
    public $data;

    /**
     * Sets the data type of the class attributes.
     */
    public function __construct()
    {
        settype($this->data, 'object');
        settype($this->message, 'object');
    }
}
