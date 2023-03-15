<?php

namespace app\controllers;

use Exception;
use core\exceptions\ApplicationException;
use core\exceptions\DatabaseException;
use core\exceptions\RequireException;

use core\controllers\Menu;
use core\controllers\View;
use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\EmployeeDAOModel;
use app\models\PersonDAOModel;

/**
 * Middleware between model and view for personal administrator.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class PersonalAdministrator
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
        View::set('pageTitle', 'Personal Administrator');

        Menu::buildMenu($_SESSION['profileId']);
        View::render('_head-elements');

        switch ($_SESSION['profileId']) {
            case 1:
                View::set('jsFile', 'personalAdministrator.min');
                View::set('jsVersion', '?v=1.0');

                View::render('_navbar');
                View::render('personalAdministrator');
                View::render('_footer');

                break;
            default:
                View::render('errors/_401-Unauthorized');
        }

        View::render('_script-elements');
    }

    /**
     * Fetches Persons information.
     *
     * @return void
     */
    public function getPersons()
    {
        $webServiceResponse = new WebServiceResponseModel();

        try {
            $personDAO = new PersonDAOModel();
            $personList = $personDAO->getAllPersons();
            $persons = [];

            foreach ($personList as $person) {
                $person['firstSurname'] = Helpers::firstLetterStringUpperCase($person['firstSurname']);
                $person['secondSurname'] = Helpers::firstLetterStringUpperCase($person['secondSurname']);
                $person['firstName'] = Helpers::firstLetterStringUpperCase($person['firstName']);

                $persons[] = $person;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Person List', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data = $persons;
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
     * Fetches Employees information.
     *
     * @return void
     */
    public function getEmployees()
    {
        $webServiceResponse = new WebServiceResponseModel();

        try {
            $employeeDAO = new EmployeeDAOModel();
            $employeeList = $employeeDAO->getAllEmployees();
            $employees = [];

            foreach ($employeeList as $employee) {
                $employee['position'] = str_replace(
                        'Ti', 'TI', Helpers::firstLetterStringUpperCase($employee['position'])
                );

                $employee['shift'] = Helpers::firstLetterStringUpperCase($employee['shift']);
                $employee['branch'] = str_replace(
                        'Cdmx', 'CDMX', Helpers::firstLetterStringUpperCase($employee['branch'])
                );

                $employees[] = $employee;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Person List', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data = $employees;
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
}
