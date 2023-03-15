<?php

namespace app\controllers;

use Exception;
use core\exceptions\ApplicationException;
use core\exceptions\AuthException;
use core\exceptions\DatabaseException;
use core\exceptions\InputDataException;
use core\exceptions\RequireException;
use core\exceptions\SessionDataException;

use core\controllers\JWT;
use core\controllers\View;

use core\models\CredentialsModel;
use core\models\MessageModel;
use core\models\WebServiceResponseModel;

use app\models\EmployeeDAOModel;
use app\models\SessionModel;
use app\models\UserDAOModel;
use app\models\UserModel;

/**
 * Middleware between model and view for user session interactions.
 *
 * @package   app\controllers
 *
 * @author    Diego Valentín
 * @copyright 2022 - Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class Session
{
    /**
     * @var object $modelHandler Connection instance handler.
     */
    private $modelHandler;

    /**
     * @var string $password Root users password.
     */
    private $password = 'cusadmin';

    /**
     * @var string $configurationFile Location of the file with the users root.
     */
    private $configurationFile = CONFIG_PATH . 'ipSession.php';

    /**
     * Default constructor.
     *
     * - Sets the type and initializes the values of the class properties.
     * - Validates that a session doesn't exist.
     *
     * @return void
     */
    public function __construct()
    {
        settype($this->modelHandler, 'object');

        if (!empty($_SESSION)) {
            header('Location: Dashboard');
        }

        $this->modelHandler = new SessionModel();
    }

    /**
     * Default method.
     *
     * Defines the interface construction and configures the properties to be used.
     *
     * @throws RequireException Will throw the exception if the view file isn't found.
     *
     * @return void
     */
    public function index()
    {
        View::set('pageTitle', 'Iniciar Sesión');
        View::set('jsFile', 'session.min');
        View::set('jsVersion', '?v=1.0');

        View::render('_head-elements');
        View::render('session');
        View::render('_script-elements');
    }

    /**
     * Try to log into the application.
     *
     * @return void
     */
    public function signIn()
    {
        $credentials = new CredentialsModel();
        $credentials->username = $_POST['username'];
        $credentials->password = $_POST['password'];

        $webServiceResponse = new WebServiceResponseModel();

        $validationStates = [];

        try {
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                throw new ApplicationException('Method Not Allowed');
            }

            $ip = Helpers::getIP();

            foreach ($credentials as $credential => $value) {
                try {
                    call_user_func_array([$this, 'validate' . ucfirst($credential)], [$credentials, $ip]);

                    $webServiceResponse->data->validation[$credential] = [
                            'result' => 'is-valid',
                            'message' => null
                    ];
                } catch (InputDataException $idEx) {
                    $webServiceResponse->data->validation[$credential] = [
                            'result' => 'is-invalid',
                            'message' => $idEx->getMessage()
                    ];

                    $validationStates[] = false;
                } catch (SessionDataException $sdEx) {
                    $webServiceResponse->data->validation[$credential] = [
                            'result' => 'is-invalid',
                            'message' => $sdEx->getMessage()
                    ];

                    $validationStates[] = false;
                } catch (RequireException $reqEx) {
                    $webServiceResponse->data->validation[$credential] = [
                            'result' => 'is-invalid',
                            'message' => $reqEx->getMessage()
                    ];

                    $validationStates[] = false;
                } catch (ApplicationException $appEx) {
                    $webServiceResponse->data->validation[$credential] = [
                            'result' => 'is-invalid',
                            'message' => $appEx->getMessage()
                    ];

                    $validationStates[] = false;
                }
            }

            if (in_array(false, $validationStates)) {
                throw new InputDataException('There are errors in the form');
            }

            $employeeDAO = new EmployeeDAOModel();
            $employeeDAO->getEmployeeByKey($credentials->username);

            $user = new UserModel();
            $user->insertUser($credentials->username, $credentials->password, $employeeDAO->position);

            $userDAO = new userDAOModel();
            $userDAO->getUserByKey($credentials->username);
            $userClients = $userDAO->getUserClientsByKey($credentials->username);

            $jwt = $this->buildAccessToken($credentials, $userDAO->profileId);

            $_SESSION['userKey'] = $credentials->username;
            $_SESSION['profileId'] = $userDAO->profileId;
            $_SESSION['clientsList'] = $userClients['clientsList'];
            $_SESSION['ipAddress'] = $ip;
            $_SESSION['accessToken'] = $jwt;

            // TODO: Remove after approving last values.
            $_SESSION['myusername'] = $credentials->username;
            $_SESSION['nivel'] = $userDAO->profileId;
            $_SESSION['clientes'] = $userClients['clientsList'];

            $this->modelHandler->insertLog($credentials->username, 1, $ip, 0, 'success');

            $webServiceResponse->statusCode = 200;
            $webServiceResponse->message = new MessageModel('success', 'Log In', 'OK');
            $webServiceResponse->accessToken = $jwt;
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
        } catch (AuthException $autEx) {
            $webServiceResponse->statusCode = 401;
            $webServiceResponse->message = new MessageModel(
                    'danger', 'Authentication mechanism', $autEx->getMessage()
            );
            http_response_code(401);
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
     * Ends the user's session.
     *
     * @throws DatabaseException    Will throw the exception if errors exist during a transaction with the
     *                              database.
     *
     * @return void
     */
    public function logout()
    {
        $this->modelHandler->updateLogout($_SESSION['userKey']);

        session_unset();
        session_destroy();

        header('Location: ' . URL . 'app/Session');
    }

    /**
     * Generates a JSON Web Token.
     *
     * @throws AuthException Will throw the exception if no token checking mechanism exists.
     *
     * @param  object  $userCredentials  Employee code and password.
     * @param  string  $profileId        User role assigned to the employee.
     *
     * @return string String representing the JWT.
     */
    private function buildAccessToken($userCredentials, $profileId)
    {
        $iat = time();
        $exp = strtotime(date('Y-m-d H:i:s', $iat) . '+' . SESSION_DURATION . ' minutes');

        # Initializes the payload that will store the contents of the access token.
        $payload = array(
                'sub' => $userCredentials->username,
                'iat' => $iat,
                'exp' => $exp,
                'rol' => $profileId,
                'cte' => 'web'
        );

        return JWT::encode($payload, CLIENT_KEY . $userCredentials->password);
    }

    /**
     * Verify user credentials through an LDAP server.
     *
     * @throws ApplicationException Will throw the exception if could not connect to LDAP server
     * @throws InputDataException   Will throw the exception if user doesn't have a defined branch.
     *
     * @param  object  $userCredentials  Employee code and password
     * @param  string  $branch           Branch where the employee carries out his activities.
     *
     * @return bool True on success or false on failure.
     */
    private function ldapAuthentication($userCredentials, $branch)
    {
        switch ($branch) {
            case 'MONTERREY':
                $domain = 'SERTECMTY';
                $ip = '192.168.130.88';
                break;
            case 'CDMX':
            case 'CDMX (INSURGENTES)':
                $domain = 'SertecDF.mx';
                $ip = '192.168.50.28';
                break;
            default:
                throw new InputDataException(
                        sprintf('User <b>%s</b> has no branch defined', $userCredentials->username)
                );
        }

        $ldapUser = $userCredentials->username . '@' . $domain;
        $ldapHost = 'ldap://' . $ip;
        $ldapPort = 389;

        $ldapConnection = @ldap_connect($ldapHost, $ldapPort);

        if (!$ldapConnection) {
            throw new ApplicationException('Could not connect to LDAP server');
        }

        ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);

        $validationResponse = @ldap_bind($ldapConnection, $ldapUser, $userCredentials->password);

        ldap_unbind($ldapConnection);

        return $validationResponse;
    }

    /**
     * Validate that the username:
     *
     *   - isn't empty
     *   - has the correct format
     *   - be active
     *   - isn't blocked by wrong attempts
     *   - isn't blocked by inactive days
     *
     * @throws InputDataException   Will throw the exception if user code hasn't the correct format.
     * @throws SessionDataException Will throw the exception if user is blocked.
     * @throws DatabaseException    Will throw the exception if errors exist during a transaction with the
     *                              database.
     *
     * @param  object  $userCredentials  Employee code and password.
     * @param  string  $ip               IP address from which the request is made.
     *
     * @return void
     */
    private function validateUsername($userCredentials, $ip)
    {
        Helpers::isEmpty($userCredentials->username);

        if (!preg_match("/[A-Z]{5}/", $userCredentials->username)) {
            throw new InputDataException('Only five capital letters of the alphabet are accepted.');
        }

        # Defines the values of the validation flags.
        $maximumWrongAttempts = 10;
        $maximumInactiveDays = 15;

        $sessionFlags = $this->modelHandler->getSessionFlags($userCredentials->username);

        if (!(int)$sessionFlags['isActive']) {
            $this->modelHandler->insertLog($userCredentials->username, 0, $ip, 1, 'Inactive User');

            throw new SessionDataException(
                    sprintf('User <b>%s</b> is not active', $userCredentials->username)
            );
        }

        if ((int)$sessionFlags['wrongAttempts'] >= $maximumWrongAttempts) {
            $this->modelHandler->insertLog(
                    $userCredentials->username, 0, $ip, 1, 'Maximum of failed attempts'
            );

            throw new SessionDataException(
                    sprintf(
                            'User <b>%s</b> is blocked for exceeding the maximum number of errors.',
                            $userCredentials->username
                    )
            );
        }

        if ((int)$sessionFlags['inactiveDays'] >= $maximumInactiveDays) {
            $this->modelHandler->insertLog($userCredentials->username, 0, $ip, 1, 'Maximum of inactive days');

            throw new SessionDataException(
                    sprintf(
                            'User <b>%s</b> is blocked for exceeding the maximum number of inactive days.',
                            $userCredentials->username
                    )
            );
        }
    }

    /**
     * Validate that the password:
     *
     *   - isn't empty
     *   - is registered in the active directory
     *
     * @throws ApplicationException Will throw the exception if could not connect to LDAP server
     * @throws DatabaseException    Will throw the exception if errors exist during a transaction with the
     *                              database.
     * @throws InputDataException   Will throw the exception if user code is empty.
     * @throws RequireException     Will throw the exception If the configuration file doesn't exist.
     * @throws SessionDataException Will throw the exception if user's password is wrong.
     *
     * @param  object  $userCredentials  Employee code and password
     * @param  string  $ip               IP address from which the request is made
     *
     * @return void
     */
    private function validatePassword($userCredentials, $ip)
    {
        Helpers::isEmpty($userCredentials->password);

        if (empty($userCredentials->username)) {
            throw new InputDataException('The user is required');
        }

        $employeeDAO = new EmployeeDAOModel;
        $employeeDAO->getEmployeeByKey($userCredentials->username);

        if (!file_exists($this->configurationFile)) {
            throw new RequireException('The configuration file is not found in the indicated path.');
        }

        $file = require $this->configurationFile;

        if (!array_key_exists($ip, $file)) {
            $isThePasswordCorrect = $this->ldapAuthentication($userCredentials, $employeeDAO->branch);
        } else {
            if ($userCredentials->password !== $this->password) {
                $isThePasswordCorrect = $this->ldapAuthentication($userCredentials, $employeeDAO->branch);
            } else {
                $isThePasswordCorrect = true;
            }
        }

        if (!$isThePasswordCorrect) {
            $this->modelHandler->insertLog($userCredentials->username, 0, $ip, 1, 'Wrong password');

            throw new SessionDataException('Incorrect password');
        }

        unset($isThePasswordCorrect, $employeeDAO);
    }
}
