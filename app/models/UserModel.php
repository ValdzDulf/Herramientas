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
}