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
use app\models\ManagerDAOModel;
use app\models\ManagerModel;

/**
 * Middleware between model and view for manager administrator.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ManagerAdministrator
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
        View::set('pageTitle', 'Manager Administrator');

        Menu::buildMenu($_SESSION['profileId']);
        View::render('_head-elements');

        switch ($_SESSION['profileId']) {
            case 1:
                View::set('jsFile', 'managerAdministrator.min');
                View::set('jsVersion', '?v=1.0');

                View::render('_navbar');
                View::render('managerAdministrator');
                View::render('_footer');

                break;
            default:
                View::render('errors/_401-Unauthorized');
        }

        View::render('_script-elements');
    }

    /**
     * Fetches Managers information.
     *
     * @return void
     */
    public function getManagers()
    {
        $webServiceResponse = new WebServiceResponseModel();

        try {
            $managerDAO = new ManagerDAOModel();
            $managerList = $managerDAO->getAllManagers();
            $managers = [];

            foreach ($managerList as $manager) {
                $firstSurname = Helpers::firstLetterStringUpperCase($manager['firstSurname']);
                $secondSurname = Helpers::firstLetterStringUpperCase($manager['secondSurname']);
                $firstName = Helpers::firstLetterStringUpperCase($manager['firstName']);

                $manager['fullName'] = "$firstSurname $secondSurname $firstName";
                $manager['actions'] = null;

                $managers[] = $manager;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Manager List', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data = $managers;
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
     * Try to update the manager's status.
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

            $manager = new ManagerModel();
            $manager->updateStatus((int)$_POST['id'], $_POST['jobCode']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Update Status', 'Successfully update information.'
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
     * Try to insert a manager.
     *
     * @return void
     */
    public function insertManager()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
            try {
                $this->validateJobCode($_POST['jobCode']);

                $employeeDAO = new EmployeeDAOModel();
                $employeeDAO->getEmployeeByKey($_POST['jobCode']);

                $managerDAO = new ManagerDAOModel();
                $dataManager = $managerDAO->getManagerByKey($_POST['jobCode']);

                if (count($dataManager) >= 1) {
                    throw new InputDataException(sprintf('<b>%s</b> already exists', $_POST['jobCode']));
                }

                $webServiceResponse->data->validation['jobCode'] = [
                        'result' => 'is-valid',
                        'message' => null
                ];
            } catch (InputDataException $idEx) {
                $webServiceResponse->data->validation['jobCode'] = [
                        'result' => 'is-invalid',
                        'message' => $idEx->getMessage()
                ];

                $validationStates[] = false;
            }

            if (in_array(false, $validationStates)) {
                throw new InputDataException('There are errors in the form');
            }

            $manager = new ManagerModel();
            $manager->insert($_POST['jobCode']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Insert', 'Successfully insert manager.'
            );
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
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
            throw new InputDataException("The manager's code is invalid");
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
}
