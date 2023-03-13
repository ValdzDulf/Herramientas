<?php

namespace core\models;

/**
 * Describes the business rules for the response messages that will be returned to the user.
 *
 * @package   core\models
 *
 * @author    Diego Valentín
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class MessageModel
{
    /**
     * @var string $type Type of message (useful for defining alert color).
     */
    public $type;

    /**
     * @var string $title Message title.
     */
    public $title;

    /**
     * @var string $description Message content.
     */
    public $description;

    /**
     * Default constructor.
     *
     * - Initializes the values of the class properties.
     *
     * @param  string  $type         Type of message (useful for defining alert color). Can be: success,
     *                               warning or danger.
     * @param  string  $title        Message title.
     * @param  string  $description  Message content.
     */
    public function __construct($type, $title, $description)
    {
        $this->type = $type;
        $this->title = $title;
        $this->description = $description;
    }
}
