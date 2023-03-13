<?php

namespace core\models;

use core\exceptions\ConfigException;
use core\exceptions\DatabaseException;
use core\exceptions\RequireException;

use mysqli;
use mysqli_result;

/**
 * Represents a connection between PHP and a Database server.
 *
 * @package   core\models
 *
 * @author    Diego Valentín
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class DatabaseConnection
{
    /**
     * @var string $configurationFile Location of the file with the connection parameters.
     */
    private $configurationFile = CONFIG_PATH . 'database.php';

    /**
     * @var mixed $dbHandler Connection resource handler.
     */
    private $dbHandler;

    /**
     * @var string  Name of connection.
     */
    private $profile;

    /**
     * @var array Parameters to connect to the database.
     */
    private $profileAttributes;

    /**
     * Initializes the attributes of the class to connect to the database.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $profile  Name of connection
     */
    public function __construct($profile)
    {
        try {
            if (!file_exists($this->configurationFile)) {
                throw new RequireException(
                        sprintf(
                                'The configuration file is not found in the indicated path: <b>%s</b>.',
                                $this->configurationFile
                        )
                );
            }

            $file = require $this->configurationFile;

            if (!array_key_exists($profile, $file)) {
                throw new ConfigException(
                        sprintf(
                                "Connection profile <b>%s</b> isn't defined.",
                                $this->configurationFile
                        )
                );
            }

            $this->profile = $profile;
            $this->profileAttributes = $file[$profile];
        } catch (RequireException $reqEx) {
            $description = DEBUG_MODE ? $reqEx->getMessage() : "Resource isn't in the specified path.";
            http_response_code(404);
            throw new DatabaseException($description);
        } catch (ConfigException $conEx) {
            $description = DEBUG_MODE ? $conEx->getMessage() : "The requested connection doesn't exist.";
            http_response_code(404);
            throw new DatabaseException($description);
        }
    }

    /**
     * Creates a connection resource and opens a connection.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     * @return void
     */
    public function connect()
    {
        try {
            switch ($this->profileAttributes['driver']) {
                case 'mysqli':
                    $this->dbHandler = new mysqli(
                            $this->profileAttributes['host'],
                            $this->profileAttributes['username'],
                            $this->profileAttributes['password'],
                            $this->profileAttributes['database']
                    );

                    if (mysqli_connect_error()) {
                        throw new DatabaseException(
                                sprintf(
                                        'Unable to connect to the database: <b>%s</b>',
                                        mysqli_connect_error()
                                )
                        );
                    }

                    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
                    mysqli_set_charset($this->dbHandler, 'utf8');

                    break;
                case 'sqlsrv':
                    $this->dbHandler = sqlsrv_connect(
                            $this->profileAttributes['host'],
                            [
                                    'Database' => $this->profileAttributes['database'],
                                    'UID' => $this->profileAttributes['username'],
                                    'PWD' => $this->profileAttributes['password']
                            ]
                    );

                    if (!$this->dbHandler) {
                        throw new DatabaseException(
                                sprintf(
                                        'Unable to connect to the database: <b>%s</b>',
                                        print_r(sqlsrv_errors(), true)
                                )
                        );
                    }

                    break;
                case 'mssql':
                    $this->dbHandler = mssql_connect(
                            $this->profileAttributes['host'],
                            $this->profileAttributes['username'],
                            $this->profileAttributes['password']
                    );

                    if (!$this->dbHandler) {
                        throw new DatabaseException(
                                sprintf(
                                        'Unable to connect to the database: <b>%s</b>',
                                        mssql_get_last_message()
                                )
                        );
                    }

                    break;
                default:
                    throw new ConfigException(
                            sprintf(
                                    "The <b>%s</b> profile doesn't have driver <b>%s</b> configured",
                                    $this->profile, $this->profileAttributes['driver']
                            )
                    );
            }
        } catch (DatabaseException $dbEx) {
            http_response_code(500);
        } catch (ConfigException $conEx) {
            $description = DEBUG_MODE ? $conEx->getMessage() : "The requested connection doesn't exist.";
            http_response_code(404);
            throw new DatabaseException($description);
        }
    }

    /**
     * Closes a previously opened database connection.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @return bool True on success or false on failure.
     */
    public function close()
    {
        switch ($this->profileAttributes['driver']) {
            case 'mysqli':
                return mysqli_close($this->dbHandler);

                break;
            case 'sqlsrv':
                return sqlsrv_close($this->dbHandler);

                break;
            case 'mssql':
                return mssql_close($this->dbHandler);

                break;
            default:
                throw new DatabaseException('No database connection exits');
        }
    }

    /**
     * Performs a query on the database.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $statement  An SQL query
     *
     * @return bool|mysqli_result|resource mysqli: mysqli_result object for select, show, describe or explain
     *                                             successfully queries, for other successful queries return
     *                                             true. Returns false on failure
     *                                     sqlsrv: A statement resource. If the statement can't be created
     *                                             false is returned.
     *                                     mssql:  Resource on success, true if no rows were returned, or
     *                                             false on error.
     */
    public function query($statement)
    {
        switch ($this->profileAttributes['driver']) {
            case 'mysqli':
                if (!$data = mysqli_query($this->dbHandler, $statement)) {
                    throw new DatabaseException(
                            sprintf(
                                    'A problem occurred during transaction execution.: <b>%s</b>',
                                    mysqli_error($this->dbHandler)
                            )
                    );
                }

                break;
            case 'sqlsrv':
                if (!$data = sqlsrv_query($this->dbHandler, $statement)) {
                    throw new DatabaseException(
                            sprintf(
                                    'A problem occurred during transaction execution.: <b>%s</b>',
                                    implode(',', sqlsrv_errors()[0])
                            )
                    );
                }

                break;
            case 'mssql':
                if (!$data = mssql_query($statement, $this->dbHandler)) {
                    throw new DatabaseException(
                            sprintf(
                                    'A problem occurred during transaction execution.: <b>%s</b>',
                                    mssql_get_last_message()
                            )
                    );
                }

                break;
            default:
                throw new DatabaseException('No database connection exits');
        }

        return $data;
    }

    /**
     * Fetches a connection resource.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $statement  An SQL query
     * @param  string  $profile    Name of connection
     *
     * @return bool|mysqli_result|resource mysqli: mysqli_result object for select, show, describe or explain
     *                                             succesfully queries, for other successful queries return
     *                                             true. Returns false on failure
     *                                     sqlsrv: A statement resource. If the statement can't be created
     *                                             false is returned.
     *                                     mssql:  Resource on success, true if no rows were returned, or
     *                                             false on error.
     */
    public static function dqlResourceStatement($statement, $profile = 'server18')
    {
        $dbInstance = new DatabaseConnection($profile);
        $dbInstance->connect();

        return $dbInstance->query($statement);
    }

    /**
     * Fetches a results row of a result set as an associative array.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $statement  An SQL query
     * @param  string  $profile    Name of connection
     *
     * @return array An array of associative arrays holding result rows.
     */
    public static function dqlStatement($statement, $profile = 'server18')
    {
        $dbInstance = new DatabaseConnection($profile);
        $dbInstance->connect();
        $resource = $dbInstance->query($statement);

        $resultSet = [];

        switch ($dbInstance->profileAttributes['driver']) {
            case 'mysqli':
                if (mysqli_num_rows($resource) === 1) {
                    $resultSet = mysqli_fetch_array($resource, MYSQLI_ASSOC);
                } else {
                    while ($row = mysqli_fetch_array($resource, MYSQLI_ASSOC)) {
                        $resultSet[] = $row;
                    }
                }

                break;
            case 'sqlsrv':
                if (sqlsrv_num_rows($resource) === 1) {
                    $resultSet = sqlsrv_fetch_array($resource, SQLSRV_FETCH_ASSOC);
                } else {
                    while ($row = sqlsrv_fetch_array($resource, SQLSRV_FETCH_ASSOC)) {
                        $resultSet[] = $row;
                    }
                }

                break;
            case 'mssql':
                if (mssql_num_rows($resource) === 1) {
                    $resultSet = mssql_fetch_array($resource, MSSQL_ASSOC);
                } else {
                    while ($row = mssql_fetch_array($resource, MSSQL_ASSOC)) {
                        $resultSet[] = $row;
                    }
                }

                break;
        }

        $dbInstance->close();

        return $resultSet;
    }

    /**
     * Gets the number of rows affected by te query.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $statement  An SQL query
     * @param  string  $profile    Name of connection
     *
     * @return false|int|string|void mysqli: Integer indicates the number of affected rows, -1
     *                                       indicates error.
     *                               sqlsrv: Integer indicates the number of affected rows modified, -1 if
     *                                       information isn't available or false on failure.
     *                               mssql:  Integer indicates the number of affected rows.
     *
     */
    public static function dmlStatement($statement, $profile = 'server18')
    {
        $dbInstance = new DatabaseConnection($profile);
        $dbInstance->connect();
        $resource = $dbInstance->query($statement);

        switch ($dbInstance->profileAttributes['driver']) {
            case 'mysqli':
                return mysqli_affected_rows($dbInstance->dbHandler);
            case 'sqlsrv':
                return sqlsrv_rows_affected($dbInstance->dbHandler);
            case 'mssql':
                return mssql_rows_affected($dbInstance->dbHandler);
        }
    }
}
