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

use app\models\ClientDAOModel;
use app\models\ManagerDAOModel;
use app\models\ClientManagerDAOModel;
use app\models\ClientManagerModel;

/**
 * Middleware between model and view for client manager administrator.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ClientManagerAdministrator
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
        View::set('pageTitle', 'Client - Manager Administrator');

        Menu::buildMenu($_SESSION['profileId']);
        View::render('_head-elements');

        switch ($_SESSION['profileId']) {
            case 1:
                View::set('jsFile', 'clientManagerAdministrator.min');
                View::set('jsVersion', '?v=1.0');

                View::render('_navbar');
                View::render('clientManagerAdministrator');
                View::render('_footer');

                break;
            default:
                View::render('errors/_401-Unauthorized');
        }

        View::render('_script-elements');
    }

    /**
     * Fetches Client Manager Relationship information.
     *
     * @return void
     */
    public function getClientManagerRelationship()
    {
        $webServiceResponse = new WebServiceResponseModel();

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new ApplicationException('Method Not Allowed');
            }

            $clientManagerDAO = new ClientManagerDAOModel();
            $relationshipList = $clientManagerDAO->getAllRelationship();
            $list = [];

            foreach ($relationshipList as $row) {
                $row['actions'] = null;

                $list[] = $row;
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Relationship List', 'OK');
            $webServiceResponse->accessToken = $_SESSION['accessToken'];
            $webServiceResponse->data = $list;
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
     * Try to insert a client manager relationship.
     *
     * @return void
     */
    public function insertClientManagerRelationship()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
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

            $clientManager = new ClientManagerModel();
            $clientManager->insertClientManagerRelationship((int)$_POST['clientId'], (int)$_POST['managerId']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Insert', 'Successfully insert relationship.'
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
     * Try to update the relationship status.
     *
     * @return void
     */
    public function updateStatus()
    {
        $webServiceResponse = new WebServiceResponseModel();
        $webServiceResponse->accessToken = $_SESSION['accessToken'];

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

            $clientManager = new ClientManagerModel();
            $clientManager->updateStatus((int)$_POST['clientId'], (int)$_POST['managerId']);

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel(
                    'success', 'Update Status', 'Successfully update information.'
            );
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
     * Validate that the id:
     *
     * - isn't empty
     * - has the correct format
     * - exists in the table client an is active.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     * @throws InputDataException Will throw an error if the id is empty or hasn't correct format.
     *
     * @param  string  $id  Unique manager identifier.
     *
     * @return void
     */
    private function validateClientId($id)
    {
        Helpers::isEmpty($id);

        if (!is_numeric($id)) {
            throw new InputDataException('The identifier is malformed');
        }

        $clientDAO = new ClientDAOModel();
        $client = $clientDAO->getClientById((int)$id);

        if (!$client || !$client['isActive']) {
            throw new InputDataException(
                    sprintf("The client with id <b>%s</b> doesn't exist or is inactive", $id)
            );
        }
    }

    /**
     * Validate that the id:
     *
     * - isn't empty
     * - has the correct format
     * - exists in the table manager and person.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     * @throws InputDataException Will throw an error if the id is empty or hasn't correct format.
     *
     * @param  string  $id  Unique manager identifier.
     *
     * @return void
     */
    private function validateManagerId($id)
    {
        Helpers::isEmpty($id);

        if (!is_numeric($id)) {
            throw new InputDataException('The identifier is malformed');
        }

        $managerDAO = new ManagerDAOModel();
        $manager = $managerDAO->getManagerById((int)$id);

        if (!$manager || !$manager['isActive']) {
            throw new InputDataException(
                    sprintf("The manager with id <b>%s</b> doesn't exist or is inactive", $id)
            );
        }
    }
}
