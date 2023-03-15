<?php

namespace app\controllers;

use core\exceptions\ApplicationException;
use core\exceptions\DatabaseException;
use core\exceptions\InputDataException;
use core\exceptions\RequireException;
use Exception;

use core\controllers\Menu;
use core\controllers\View;

use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\EmployeeDAOModel;
use app\models\PersonDAOModel;
use app\models\UserDAOModel;
use app\models\UserModel;

/**
 * Middleware between model and view for dashboard interactions.
 *
 * @package   app\controllers
 *
 * @author    Alberto Contreras
 * @author    Diego Valentín
 * @copyright 2021 - 2023 Management Information System
 *
 * @version   1.1.0
 * @since     1.1.0 Fetches data from DAO models.
 * @since     1.0.0 First time this was introduced.
 */
class Dashboard
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
        View::set('pageTitle', 'Dashboard');
        View::set('jsFile', 'dashboard.min');
        View::set('jsVersion', '?v=1.0');
        Menu::buildMenu($_SESSION['profileId']);

        View::render('_head-elements');
        View::render('_navbar');
        View::render('dashboard');
        View::render('_script-elements');
    }

    /**
     * Fetches the user's information.
     *
     * @return void
     */
    public function getUserInformation()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $userKey = $_SESSION['userKey'];

        # Personnel based on organizational levels.
        $corporateDistribution = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new ApplicationException('Method Not Allowed');
            }

            $employeeDAO = new EmployeeDAOModel();
            $employeeDAO->getEmployeeByKey($userKey);

            $corporateProfiles = ['tenuredDirectorCode', 'directorCode', 'managerCode', 'supervisorCode'];

            foreach ($employeeDAO as $key => $value) {
                if (in_array($key, $corporateProfiles, true)) {
                    $personDAO = new PersonDAOModel();
                    $personDAO->getPersonByKey($value);

                    $corporateDistribution[$key] = $personDAO;

                    unset($personDAO);
                }
            }

            $personDAO = new PersonDAOModel();
            $personDAO->getPersonByKey($userKey);

            $userDAO = new UserDAOModel();
            $userDAO->getUserByKey($userKey);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get user data', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data->corporateDistribution = $corporateDistribution;
            $webServiceResponse->data->person = $personDAO;
            $webServiceResponse->data->employee = $employeeDAO;
            $webServiceResponse->data->user = $userDAO;
            $webServiceResponse->data->clients = explode(',', str_replace("'", '', $_SESSION['clientsList']));
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method', $appEx->getMessage()
            );
            http_response_code(405);
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
     * Try toupdate the user's contact data.
     *
     * @return void
     */
    public function updateUserContactData()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $userKey = $_SESSION['userKey'];
        $validationStates = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new ApplicationException('Method Not Allowed');
            }

            foreach ($_POST as $field => $value) {
                try {
                    call_user_func_array([$this, 'validate' . ucfirst($field)], [$value]);

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
            $user->updateContactData($userKey, $_POST['email'], $_POST['phoneExtension']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Update Data', 'Datos actualizados');
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
     * Validate that the email:
     *
     *   - isn't empty
     *   - has the correct format
     *
     * @throws InputDataException   Will throw the exception if email hasn't the correct format.
     *
     * @param  string  $email  User's email.
     *
     * @return void
     */
    private function validateEmail($email)
    {
        Helpers::isEmpty($email);
        Helpers::isMailFromSertecDomain($email);
    }

    /**
     * Validate that the phone extension:
     *
     *   - isn't empty
     *   - has the correct format
     *
     * @throws InputDataException   Will throw the exception if email hasn't the correct format.
     *
     * @param  string  $phoneExtension  User's phone extension.
     *
     * @return void
     */
    private function validatePhoneExtension($phoneExtension)
    {
        Helpers::isEmpty($phoneExtension);
        Helpers::isValidExtension($phoneExtension);
    }
}
