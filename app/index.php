<?php

use core\exceptions\RequireException;

use core\models\MessageModel;

/**
 * --------------------------------------------------------------------------
 * Loads the global functions of the Framework. All web request are directed
 * to this point.
 * --------------------------------------------------------------------------
 */

# Enables the use of session variables.
session_start();

# Time zone and locale information.
date_default_timezone_set('America/Mexico_City');
setlocale(LC_ALL, 'spanish');

# Host running the application.
define('HOST', $_SERVER['HTTP_HOST'], true);

# Working environment according to the host running the application.
const ENVIRONMENT = (HOST === '<productionIP>' || HOST === 'localhost') ? 'production' : 'development';
const BRANCH = (HOST === '<productionIP>' || HOST === 'localhost') ? 'PRODUCCION' : 'DESARROLLO'; // TODO: Eliminate constant after migrating usages to ENVIRONMENT

# Error detection mode.
const DEBUG_MODE = ENVIRONMENT === 'development' ? 1 : 0;

# Defines the type of message to be reported by the PHP interpreter.
error_reporting(E_ALL ^ E_NOTICE);

# Determines whether PHP interpreter runtime messages should be displayed.
ini_set('display_errors', (string)DEBUG_MODE);

# Transfer protocol.
define('REQUEST_SCHEME', $_SERVER['REQUEST_SCHEME'], true);

# Path of the directory where the project is hosted.
define('PROJECT_PATH', dirname(__DIR__), true);
define("PROJECTPATH", dirname(__DIR__), true); // TODO: TODO: Eliminate constant after migrating usages to PROJECT_PATH

/**
 * Path where the application is executed.
 */
const APPLICATION_PATH = PROJECT_PATH . '/app/';
define('APPPATH', PROJECTPATH . '/app', true); // TODO: TODO: Eliminate constant after migrating usages to APPLICATION_PATH

/**
 * Path where the configuration files are stored.
 */
const CONFIG_PATH = PROJECT_PATH . '/config/';

/**
 * MVC execution path.
 */
const URL = REQUEST_SCHEME . '://' . HOST . '/herramientas/';

/**
 * Path where the third party resources are stored.
 */
const RESOURCES_PATH = URL . 'public/resources/';
const VENDOR_PATH = URL . 'vendor/';

/**
 * Path where the multimedia resources are stored.
 */
const MULTIMEDIA_PATH = URL . 'public/images/';

/**
 * Path where the application resources are stored.
 */
const APP_RESOURCES_PATH = URL . 'app/assets/';

/**
 * Path where historical files are stored.
 */
const STORAGE_PATH = PROJECT_PATH . '/storage/';

/**
 * Path where the log files are stored.
 */
const LOG_PATH = PROJECT_PATH . '/log/';

/**
 * Path where the temporary files are stored.
 */
const TMP_PATH = PROJECT_PATH . '/tmp/';

/**
 * Session duration for the JWT.
 */
const SESSION_DURATION = '20';

/**
 * Secret password for JWT.
 */
const CLIENT_KEY = '<clientKey>';

define('DOCUMENT_ROOT', $_SERVER['DOCUMENT_ROOT'], true); // TODO: Validate where the constant is used.

# Automatic loading of the classes used.
spl_autoload_register(static function ($class) {
    try {
        $className = str_replace('\\', '/', PROJECT_PATH . '/' . $class . '.php');

        if (!is_readable($className)) {
            throw new RequireException (
                    sprintf(
                            "The file that defines the class <b>%s</b> isn't found in the specified path.",
                            $class
                    )
            );
        }

        include_once $className;
    } catch (RequireException $reqEx) {
        $description = DEBUG_MODE ?
                $reqEx->getMessage() : "The file that defines the class $class is not found";

        $message = new MessageModel('danger', 'Resource not found', $description);

        echo json_encode($message, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        http_response_code(404);
    }
});

# Execute the request.
$requester = new core\controllers\Requester();
