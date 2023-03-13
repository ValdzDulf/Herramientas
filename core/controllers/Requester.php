<?php

namespace core\controllers;

use Exception;
use core\exceptions\ApplicationException;
use core\exceptions\InterfaceAvailableException;
use core\exceptions\RequireException;

use core\models\MessageModel;

/**
 * Parses the URIs and determines the method of the class to execute.
 *
 * @package   core\controllers
 *
 * @author    Erick Pulido
 * @author    Diego Valentín
 * @copyright 2020 - 2022 Management Information System
 *
 * @version   2.0.0
 * @since     2.0.0 Implements exception handling and error messaging for production and development
 *            environments.
 * @since     1.0.0 First time this was introduced.
 */
class Requester
{
    /**
     * @var object $controller Declaration in the controller namespace.
     */
    private $controller;

    /**
     * @var string $method Name of the method to be executed.
     */
    private $method = 'index';

    /**
     * @var array $args Arguments to be used in the method.
     */
    private $args = [];

    /**
     * Path of the namespace where the controller is located.
     */
    const NAMESPACE_CONTROLLER = 'app\controllers\\';

    /**
     * Path where the controllers are located.
     */
    const APP_CONTROLLERS = APPLICATION_PATH . 'controllers/';

    /**
     * Default constructor.
     *
     * Instances to the controller class and executes the method from the given URL.
     *
     * @return void
     */
    public function __construct()
    {
        $url = $this->parseUrl();

        try {
            $this->controller = ucfirst($url[0]);

            if (!file_exists(self::APP_CONTROLLERS . $this->controller . '.php')) {
                throw new RequireException(
                        sprintf("The file defining the <b>%s</b> class isn't found.", $this->controller)
                );
            }

            $namespaceController = self::NAMESPACE_CONTROLLER . $this->controller;

            if (!class_exists($namespaceController)) {
                throw new InterfaceAvailableException(
                        sprintf("The requested class <b>%s</b> isn't defined.", $this->controller)
                );
            }

            $this->controller = new $namespaceController;
            $this->method = isset($url[1]) ? $url[1] : $this->method;

            if (!method_exists($this->controller, $this->method)) {
                throw new InterfaceAvailableException(
                        sprintf(
                                "The requested method <b>%s</b> isn't defined in the class <b>%s</b>.",
                                $this->method, $url[0]
                        )
                );
            }

            unset($url[0], $url[1]);

            $this->args = $url ? array_values($url) : $this->args;

            $this->render();
        } catch (RequireException $reqEx) {
            $description = DEBUG_MODE
                    ? $reqEx->getMessage() : "The resource isn't found in the indicated path.";

            $messageModel = new MessageModel('danger', 'Resource not found', $description);
            http_response_code(404);

            echo json_encode(
                    $messageModel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } catch (InterfaceAvailableException $intEx) {
            $description = DEBUG_MODE ? $intEx->getMessage() : 'Method or class is not defined.';

            $messageModel = new MessageModel('danger', 'Interface not available', $description);
            http_response_code(404);

            echo json_encode(
                    $messageModel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } catch (Exception $ex) {
            $messageModel = new MessageModel('danger', 'Exception not identified', $ex->getMessage());
            http_response_code(400);

            echo json_encode(
                    $messageModel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        } finally {
            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Transforms a URL into an indexed array.
     *
     * @return array|false|string[] Array containing string, empty array if delimiter isn't in string or
     *                              false if delimiter is empty.
     */
    private function parseUrl()
    {
        $array = [];

        try {
            if (!isset($_GET['url'])) {
                throw new ApplicationException("The resource to be used hasn't been defined");
            }

            $array = explode('/', filter_var(rtrim($_GET['url'], '/'), FILTER_SANITIZE_URL));
        } catch (ApplicationException $appEx) {
            $description = DEBUG_MODE ? $appEx->getMessage() : "URL isn't defined or is empty";

            $messageModel = new MessageModel('danger', 'Malformed URL', $description);
            http_response_code(500);

            echo json_encode(
                    $messageModel, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );
        }

        return $array;
    }

    /**
     * Calls the method of the class with the specified parameters.
     *
     * @return void
     */
    private function render()
    {
        call_user_func_array([$this->controller, $this->method], $this->args);
    }
}
