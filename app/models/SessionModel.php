<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for interaction with the session data.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class SessionModel
{
    /**
     * Fetches data from the following status flags
     *
     *   - the user's active
     *   - the user hasn't exceeded the maximum allowed number of failed attempts.
     *   - The user hasn't exceeded the maximum number of inactive days allowed.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return array An associative array holding the status flags.
     */
    public function getSessionFlags($jobCode)
    {
        $statement = "
            SELECT
                IF (userT.jobCode IS NULL, 1, userT.isActive) AS isActive,
                SUM(IF(logT.isWrongAttempt = 1, 1, 0)) AS wrongAttempts,
                sessionDataT.lastAccessDate AS lastAccessDate,
                DATEDIFF(CURRENT_TIMESTAMP, sessionDataT.lastAccessDate) AS inactiveDays
            FROM
                data.User AS userT
            LEFT JOIN
                archive.SessionLog AS logT
                ON userT.jobCode = logT.jobCode
            LEFT JOIN (
                SELECT
                    IF (jobCode IS NULL, '$jobCode', jobCode) AS jobCode,
                    IF (MAX(loginDate) IS NULL, CURRENT_TIMESTAMP, MAX(loginDate)) AS lastAccessDate
                FROM
                    archive.SessionLog
                WHERE
                    isWrongAttempt = 0
                    AND jobCode = '$jobCode'
            ) AS sessionDataT
                ON userT.jobCode = sessionDataT.jobCode
            WHERE
                userT.jobCode = '$jobCode'
                AND logT.loginDate >= sessionDataT.lastAccessDate;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Inserts the login activity in the log.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string   $jobCode             Unique employee code.
     * @param  integer  $isLogin             Flag to indicate if the user has an active session.
     * @param  string   $ip                  IP address from which the request is made.
     * @param  integer  $isWrongAttempt      Flag to indicate if the session attempt was successful.
     * @param  string   $descriptionAttempt  Session attempt details.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function insertLog($jobCode, $isLogin, $ip, $isWrongAttempt, $descriptionAttempt)
    {
        $statement = "
            INSERT INTO archive.SessionLog
                (jobCode, loginDate, ipAddress, isLogin, isWrongAttempt, descriptionAttempt)
            VALUES
                ('$jobCode', CURRENT_TIMESTAMP, '$ip', $isLogin, $isWrongAttempt, '$descriptionAttempt');
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Updates the logout activity in the log.
     *
     * @throws DatabaseException Will throw the exception if there are errors during transaction with
     *                           the database.
     *
     * @param  string  $jobCode  Unique employee code.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateLogout($jobCode)
    {
        $statement = "
            UPDATE
                archive.SessionLog
            SET
                logoutDate = CURRENT_TIMESTAMP,
                isLogin = 0
            WHERE
                jobCode = '$jobCode'
            ORDER BY
                loginDate DESC
            LIMIT 1;
        ";

        return DatabaseConnection::dmlStatement($statement);
    }
}
