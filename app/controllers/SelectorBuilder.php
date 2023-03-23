<?php

namespace app\controllers;

use Exception;
use core\exceptions\ApplicationException;

use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\ManagerDAOModel;
use app\models\ProfileDAOModel;

/**
 * Middleware between model and view for the construction of select type elements.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2023 - Management Information System (Development Team)
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class SelectorBuilder
{
    /**
     * Builds the options for the active profile selector.
     *
     * @return void
     */
    public function getActiveProfiles()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $webServiceResponse->data = [];
        $webServiceResponse->data[] = ['extra' => null, 'value' => '', 'text' => 'Select an option'];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new ApplicationException('Method Not Allowed');
            }

            $profileDAO = new ProfileDAOModel();
            $list = $profileDAO->getActiveProfiles();

            foreach ($list as $profile) {
                $text = $profile['descriptiveName'];
                $value = $profile['id'];
                $extra = null;

                $webServiceResponse->data[] = ['extra' => $extra, 'value' => $value, 'text' => $text];
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Profile List', 'OK');
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method', $appEx->getMessage()
            );
            http_response_code(405);
        } catch (Exception $ex) {
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            $webServiceResponse->statusCode = 400;
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
     * Builds the options for the active manager selector.
     *
     * @return void
     */
    public function getActiveManagers()
    {
        $webServiceResponse = new WebServiceResponseModel();

        $webServiceResponse->data = [];
        $webServiceResponse->data[] = ['extra' => null, 'value' => '', 'text' => 'Select an option'];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
                throw new ApplicationException('Method Not Allowed');
            }

            $managerDAO = new ManagerDAOModel();
            $list = $managerDAO->getActiveManagers();

            foreach ($list as $manager) {
                $manager['firstSurname'] = Helpers::firstLetterStringUpperCase($manager['firstSurname']);
                $manager['secondSurname'] = Helpers::firstLetterStringUpperCase($manager['secondSurname']);
                $manager['firstName'] = Helpers::firstLetterStringUpperCase($manager['firstName']);

                $text = "{$manager['firstSurname']} {$manager['secondSurname']} {$manager['firstName']}";
                $value = $manager['id'];
                $extra = null;

                $webServiceResponse->data[] = ['extra' => $extra, 'value' => $value, 'text' => $text];
            }

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Get Manager List', 'OK');
            http_response_code(200);
        } catch (ApplicationException $appEx) {
            $webServiceResponse->statusCode = 405;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Unauthorized HTTP Method', $appEx->getMessage()
            );
            http_response_code(405);
        } catch (Exception $ex) {
            $webServiceResponse->message = new MessageModel('danger', 'Uncaught Exception', $ex->getMessage());
            $webServiceResponse->statusCode = 400;
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
