<?php

namespace app\models;

use core\exceptions\DatabaseException;
use core\exceptions\InputDataException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the user's data
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022  Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class UserDAOModel
{
    /**
     * @var integer Unique user identifier.
     */
    public $id;

    /**
     * @var integer User role assigned to the employee.
     */
    public $profileId;

    /**
     * @var string Unique employee code.
     */
    public $jobCode;

    /**
     * @var string Corporate mail.
     */
    public $email;

    /**
     * @var string Corporate phone extension.
     */
    public $phoneExtension;

    /**
     * Fetches all users no matter their activation status.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the person list.
     */
    public function getAllUsers()
    {
        $statement = "
            SELECT
                userT.id,
                profileT.id AS profileId,
                descriptiveName,
                userT.jobCode,
                email,
                phoneExtension,
                userT.isActive,
                logT.wrongAttempts,
                sessionData.lastSuccessAccessDate,
                DATEDIFF(CURRENT_TIMESTAMP, sessionData.lastSuccessAccessDate) AS inactiveDays
            FROM
                data.User as userT
            INNER JOIN
                data.Profile as profileT
                ON userT.profileId = profileT.id
            LEFT JOIN
                (
                    SELECT
                        jobCode,
                        SUM(isWrongAttempt) AS wrongAttempts
                    FROM
                        archive.SessionLog
                    WHERE
                        isWrongAttempt = 1
                    GROUP BY
                        jobCode
                ) AS logT
                ON userT.jobCode = logT.jobCode
            LEFT JOIN
                (
                    SELECT
                        jobCode,
                        MAX(loginDate) AS lastSuccessAccessDate
                    FROM
                        archive.SessionLog
                    WHERE
                        isWrongAttempt = 0
                        AND descriptionAttempt = 'success'
                    GROUP BY
                        jobCode
                ) AS sessionData
                ON userT.jobCode = sessionData.jobCode
            ORDER BY userT.id;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the user's data using his unique code.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     * @throws InputDataException Will throw the exception if the user doesn't exist.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return void
     */
    public function getUserByKey($jobCode)
    {
        $statement = "
            SELECT
                id,
                profileId,
                jobCode,
                email,
                phoneExtension
            FROM
                data.User
            WHERE
                jobCode = '$jobCode'
                AND isActive = 1;
        ";

        $data = DatabaseConnection::dqlStatement($statement);

        if (count($data) === 0) {
            throw new InputDataException(sprintf("User [%s] doesn't exist", $jobCode));
        }

        $this->setUser($data);
    }

    /**
     * Fetches the list of clients available to the user.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return array List of clients.
     */
    public function getUserClientsByKey($jobCode)
    {
        $statement = "
            SELECT
                CONCAT(
                    '\'',
                    GROUP_CONCAT(DISTINCT(ClientT.internalKey) ORDER BY ClientT.internalKey ASC SEPARATOR '\',\''),
                    '\''
                ) AS clientsList
            FROM
                data.Client AS ClientT
            INNER JOIN
                data.ClientManager AS CliManT
                ON ClientT.id = CliManT.clientId
            INNER JOIN
                data.Manager AS ManagerT
                ON CliManT.managerId = ManagerT.id
            INNER JOIN (
                SELECT
                    directorCode,
                    managerCode
                FROM
                    data.Employee
                WHERE
                    directorCode LIKE (
                        SELECT
                            IF (profileId = 7, User.jobCode, '%')
                        FROM
                            data.User
                        WHERE
                            User.jobCode = '$jobCode'
                    )
                    AND managerCode LIKE (
                        SELECT
                            CASE
                                WHEN profileId BETWEEN 1 AND 7 THEN '%'
                                WHEN profileId = 8 THEN User.jobCode
                                ELSE (SELECT managerCode FROM data.Employee WHERE jobCode = '$jobCode')
                            END
                        FROM
                            data.User
                        WHERE
                            User.jobCode = '$jobCode'
                    )
            ) AS EmployeeT
                ON EmployeeT.managerCode = ManagerT.jobCode
            WHERE
                ClientT.isActive = 1
                AND CliManT.isActive = 1
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Set the user entity attributes.
     *
     * @param  array  $data  User's data.
     *
     * @return void
     */
    private function setUser($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }
}
