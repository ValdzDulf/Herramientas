<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for interaction with the manager data.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ManagerModel
{
    /**
     * Updates the manager's status.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $id       Unique manager identifier.
     * @param  string   $jobCode  Unique user key.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateStatus($id, $jobCode)
    {
        $statement = "
            UPDATE
                data.Manager
            SET
                isActive = IF (isActive = 0, 1, 0)
            WHERE
                id = $id
                AND jobCode = '$jobCode';
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Try to insert a new manager.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $jobCode  Unique manager identifier.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function insert($jobCode)
    {
        $statement = "
            INSERT INTO data.Manager
                (jobCode)
            VALUES
                ('$jobCode');
        ";

        return DatabaseConnection::dmlStatement($statement);
    }
}
