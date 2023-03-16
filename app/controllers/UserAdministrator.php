<?php

namespace app\controllers;

use Exception;
use core\exceptions\ApplicationException;
use core\exceptions\DatabaseException;
use core\exceptions\InputDataException;
use core\exceptions\RequireException;

use core\controllers\Menu;
use core\controllers\View;

use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\EmployeeDAOModel;
use app\models\UserDAOModel;
use app\models\UserModel;

/**
 * Middleware between model and view for user administrator.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 - Management Information System (Development Team)
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class UserAdministrator
{
    /**
     * Default constructor.
     *
     * - Validates that a session exist.
     *
     * @return void
     */
    public function __construct()
    {
        if (empty($_SESSION)) {
            header('Location: Session');
            die;
        }
    }

    /**
     * Default method.
     *
     * Defines the interface construction and configures the properties to be used.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     * @throws RequireException  Will throw the exception if the view file isn't found.
     *
     * @return void
     */
    public function index()
    {
        View::set('pageTitle', 'User Administrator');

        Menu::buildMenu($_SESSION['profileId']);
        View::render('_head-elements');

        switch ($_SESSION['profileId']) {
            case 1:
                View::set('jsFile', 'userAdministrator.min');
                View::set('jsVersion', '?v=1.0');

                View::render('_navbar');
                View::render('userAdministrator');
                View::render('_footer');

                break;
            default:
                View::render('errors/_401-Unauthorized');
        }

        View::render('_script-elements');
    }

    /**
     * Fetches users information.
     *
     * @return void
     */
    public function getUsers()
    {
        $webServiceResponse = new WebServiceResponseModel();

        try {
            $userDAO = new UserDAOModel();
            $userList = $userDAO->getAllUsers();
            $users = [];

            foreach ($userList as $user) {
                $user['actions'] = null;

                $users[] = $user;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Person List', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data = $users;
            http_response_code(200);
        } catch (Exception $ex) {
            $webServiceResponse->statusCode = 400;
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            http_response_code(400);
        } finally {
            echo json_encode(
                    $webServiceResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Try to unlock a user.
     *
     * @return void
     */
    public function unlockUser()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new ApplicationException('Method Not Allowed');
            }

            foreach ($_POST as $field => $value) {
                try {
                    @call_user_func_array([$this, 'validate' . ucfirst($field)], [$value]);

                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-valid',
                            'message' => null
                    ];
                } catch (InputDataException $idEx) {
                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-invalid',
                            'message' => $idEx->getMessage()
                    ];

                    $validationStates[] = false;
                }
            }

            if (in_array(false, $validationStates)) {
                throw new InputDataException('There are errors in the form');
            }

            $user = new UserModel();

            switch ($_POST['type']) {
                case 'wrongAttempt':
                    $user->unlockWrong(
                            $_POST['jobCode'], date('Y-m-d', strtotime($_POST['lastSuccessAccessDate']))
                    );

                    break;
                case 'inactiveDays':
                    $user->unlockInactiveDays(
                            $_POST['jobCode'], date('Y-m-d', strtotime($_POST['lastSuccessAccessDate']))
                    );

                    break;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Unlock User', 'User successfully unlocked.'
            );
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            http_response_code(200);
        } catch (InputDataException $idEx) {
            $webServiceResponse->statusCode = 422;
            $webServiceResponse->message = new MessageModel('danger', 'Input Data', $idEx->getMessage());
            http_response_code(422);
        } catch (Exception $ex) {
            $webServiceResponse->statusCode = 400;
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            http_response_code(400);
        } finally {
            echo json_encode(
                    $webServiceResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Try to update the user's profile.
     *
     * @return void
     */
    public function updateProfile()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new ApplicationException('Method Not Allowed');
            }

            foreach ($_POST as $field => $value) {
                try {
                    @call_user_func_array([$this, 'validate' . ucfirst($field)], [$value]);

                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-valid',
                            'message' => null
                    ];
                } catch (InputDataException $idEx) {
                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-invalid',
                            'message' => $idEx->getMessage()
                    ];

                    $validationStates[] = false;
                }
            }

            if (in_array(false, $validationStates)) {
                throw new InputDataException('There are errors in the form');
            }

            $userDAO = new UserDAOModel();
            $userDAO->getUserByKey($_POST['jobCode']);

            $user = new UserModel();
            $user->updateProfile($userDAO->id, (int)$_POST['profileId']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Update Profile', 'Successfully update information.'
            );
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method', $appEx->getMessage()
            );
            http_response_code(405);
        } catch (InputDataException $idEx) {
            $webServiceResponse->statusCode = 422;
            $webServiceResponse->message = new MessageModel('danger', 'Input Data', $idEx->getMessage());
            http_response_code(422);
        } catch (Exception $ex) {
            $webServiceResponse->statusCode = 400;
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            http_response_code(400);
        } finally {
            echo json_encode(
                    $webServiceResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Try to update the user's status.
     *
     * @return void
     */
    public function updateStatus()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new ApplicationException('Method Not Allowed');
            }

            foreach ($_POST as $field => $value) {
                try {
                    @call_user_func_array([$this, 'validate' . ucfirst($field)], [$value]);

                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-valid',
                            'message' => null
                    ];
                } catch (InputDataException $idEx) {
                    $webServiceResponse->data->validation[$field] = [
                            'result' => 'is-invalid',
                            'message' => $idEx->getMessage()
                    ];

                    $validationStates[] = false;
                }
            }

            if (in_array(false, $validationStates)) {
                throw new InputDataException('There are errors in the form');
            }

            $user = new UserModel();
            $user->updateStatus((int)$_POST['id'], $_POST['jobCode']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Update Status', 'Successfully update information.'
            );
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method', $appEx->getMessage()
            );
            http_response_code(405);
        } catch (InputDataException $idEx) {
            $webServiceResponse->statusCode = 422;
            $webServiceResponse->message = new MessageModel('danger', 'Input Data', $idEx->getMessage());
            http_response_code(422);
        } catch (Exception $ex) {
            $webServiceResponse->statusCode = 400;
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            http_response_code(400);
        } finally {
            echo json_encode(
                    $webServiceResponse, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
            );

            # Clears explicitly the variables used.
            foreach (get_defined_vars() as $key => $var) {
                unset(${$key});
            }
        }
    }

    /**
     * Validate that the id:
     *
     * - isn't empty
     * - has the correct format
     *
     * @throws InputDataException Will throw an error if the id is empty or hasn't correct format.
     *
     * @param  string  $id  Unique user identifier.
     *
     * @return void
     */
    private function validateId($id)
    {
        Helpers::isEmpty($id);

        if (!is_numeric($id)) {
            throw new InputDataException('The identifier is malformed');
        }
    }

    /**
     * Validate that the jobCode:
     *
     * - isn't empty
     * - has the correct format
     *
     * @throws InputDataException Will throw an error if the jobCode is empty or hasn't correct format.
     *
     * @param  string  $jobCode  Unique user code.
     *
     * @return void
     */
    private function validateJobCode($jobCode)
    {
        Helpers::isEmpty($jobCode);

        if (!preg_match("/[A-Z]{5}/", $jobCode)) {
            throw new InputDataException("The user's code is invalid");
        }
    }

    /**
     * Validate that the profile id:
     *
     * - isn't empty
     * - has the correct format
     *
     * @throws InputDataException Will throw an error if the id is empty or hasn't correct format.
     *
     * @param  string  $id  Unique profile identifier.
     *
     * @return void
     */
    private function validateProfileId($id)
    {
        Helpers::isEmpty($id);

        if (!is_numeric($id)) {
            throw new InputDataException("The identifier isn't numeric");
        }
    }

    /**
     * Validate that the date:
     *
     * - isn't empty
     * - has the correct format
     *
     * @throws InputDataException Will throw an error if the id is empty or hasn't correct format.
     *
     * @param  string  $date  Last success access date.
     *
     * @return void
     */
    private function validateLastSuccessAccessDate($date)
    {
        Helpers::isEmpty($date);

        if (!date('Y-m-d', strtotime($date))) {
            throw new InputDataException("Isn't a valid date");
        }
    }
}
