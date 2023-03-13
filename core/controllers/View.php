<?php

namespace core\controllers;

use core\exceptions\RequireException;

use core\models\MessageModel;

/**
 * Retrieves files and attributes for the display of an interface.
 *
 * @package   core\controllers
 *
 * @author    Erick Pulido
 * @author    Diego Valentín
 * @copyright 2020 - 2022 Management Information System
 *
 * @version   2.0.0
 * @since     2.0.0 Adds error exception handling and HTTP response codes.
 * @since     1.0.0 First time this was introduced.
 */
class View
{
    /**
     * @var array $data Attributes available in the interface.
     */
    protected static $data;

    /**
     * @var string $pathApplicationView Location where application views are located.
     */
    protected static $pathAppView = '/app/views/';

    /**
     * @var string $pathCoreView Location where application views are located.
     */
    protected static $pathCoreView = '/core/views/';

    /**
     * Includes the view file and initializes the specified attributes.
     *
     * @throws RequireException Will throw the exception if the view file isn't found.
     *
     * @param  string  $viewName  File name of the view to be displayed.
     *
     * @return void
     */
    public static function render($viewName)
    {
        try {
            $path = str_replace('\\', '/', PROJECT_PATH . self::$pathCoreView . $viewName . '.php');

            if (!file_exists($path)) {
                $path = str_replace('\\', '/', PROJECT_PATH . self::$pathAppView . $viewName . '.php');
                if (!file_exists($path)) {
                    throw new RequireException(sprintf('The <b>%s.php</b> file is not found', $viewName));
                }
            };

            # Activates output buffering.
            ob_start();

            # Converts the received arguments into variables.
            if (self::$data !== null) {
                extract(self::$data, 0);
            }

            @include $path;

            # Gets the contents of the output buffer and deletes it.
            echo ob_get_clean();
        } catch (RequireException $reqEx) {
            $description = DEBUG_MODE ?
                    $reqEx->getMessage() : "The resource isn't found in the indicated path.";

            $messageModel = new MessageModel('danger', 'Resource not found', $description);
            http_response_code(404);

            echo json_encode(
                    $messageModel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }
    }

    /**
     * Adds to the data array an attribute of the form key => value.
     *
     * @param  string  $key    Argument identifier name
     * @param  mixed   $value  Argument value
     *
     * @return void
     */
    public static function set($key, $value)
    {
        self::$data[$key] = $value;
    }
}
