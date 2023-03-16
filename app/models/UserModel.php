<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for interaction with the user data.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class UserModel
{
    /**
     * Inserts the user's registration if it doesn't exist, otherwise, update his password.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $jobCode   Unique employee code.
     * @param  string  $passCode  User's password.
     * @param  string  $position  Position held by the employee.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function insertUser($jobCode, $passCode, $position)
    {
        $statement = "
            INSERT INTO data.User
                (jobCode, passCode, profileId)
            VALUES
                (
                    '$jobCode',
                    AES_ENCRYPT('$passCode', SHA2('KlNlcnRlYzIwMjIj', 512)),
                    CASE
                        WHEN '$position' LIKE 'DIRECTOR%' THEN 7
                        WHEN '$position' LIKE 'GERENTE%' THEN 8
                        WHEN '$position' LIKE 'SUPERVISOR%' THEN 9
                        WHEN '$position' LIKE 'AUXILIAR%' THEN 10
                        ELSE 17
                    END
                )
            ON DUPLICATE KEY UPDATE
                passCode = VALUES(passCode);
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the rows for the blocking of erroneous attempts.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $jobCode                Unique employee code.
     * @param  string  $lastSuccessAccessDate  Last successful login date.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function unlockWrong($jobCode, $lastSuccessAccessDate)
    {
        $statement = "
            UPDATE
                archive.SessionLog
            SET
                loginDate = CURRENT_TIMESTAMP,
                isWrongAttempt = 0,
                descriptionAttempt = 'Unlock'
            WHERE
                jobCode = '$jobCode'
                AND isWrongAttempt = 1
                AND DATE(loginDate) <= '$lastSuccessAccessDate';
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Update the row for inactive days blocking.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $jobCode                Unique employee code.
     * @param  string  $lastSuccessAccessDate  Last successful login date.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function unlockInactiveDays($jobCode, $lastSuccessAccessDate)
    {
        $statement = "
            UPDATE
                archive.SessionLog
            SET
                loginDate = CURRENT_TIMESTAMP,
                isWrongAttempt = 0,
                descriptionAttempt = 'success'
            WHERE
                jobCode = '$jobCode'
                AND DATE(loginDate) <= '$lastSuccessAccessDate'
            ORDER BY
                id DESC
            LIMIT 1
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the user's contact information.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $jobCode         Unique user key.
     * @param  string  $email           User's email.
     * @param  string  $phoneExtension  User's telephone extension.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateContactData($jobCode, $email, $phoneExtension)
    {
        $statement = "
            UPDATE
                data.User
            SET
                email = '$email',
                phoneExtension = $phoneExtension
            WHERE
                jobCode = '$jobCode';
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the user's status.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $id       Unique user identifier.
     * @param  string   $jobCode  Unique user key.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateStatus($id, $jobCode)
    {
        $statement = "
            UPDATE
                data.User
            SET
                isActive = IF (isActive = 0, 1, 0)
            WHERE
                id = $id
                AND jobCode = '$jobCode';
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the user's profile.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $userId     Unique user identifier.
     * @param  integer  $profileId  Unique profile identifier.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateProfile($userId, $profileId)
    {
        $statement = "
            UPDATE
                data.User
            SET
                profileId = $profileId
            WHERE
                id = $userId;
        ";

        return DatabaseConnection::dmlStatement($statement);
    }
}
