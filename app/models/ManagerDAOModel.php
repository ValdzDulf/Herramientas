<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the manager's data
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ManagerDAOModel
{
    /**
     * Fetches all managers, no matter their activation status.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the manager list.
     */
    public function getAllManagers()
    {
        $statement = "
            SELECT
                managerT.id,
                managerT.jobCode,
                firstSurname,
                secondSurname,
                firstName,
                managerT.isActive
            FROM
                data.Manager AS managerT
            INNER JOIN
                data.Person AS personT
                ON personT.jobCode = managerT.jobCode
            ORDER BY
                managerT.id;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the manager's data using his unique id.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the manager list.
     */
    public function getManagerById($id)
    {
        $statement = "
            SELECT
                managerT.id,
                managerT.jobCode,
                firstSurname,
                secondSurname,
                firstName,
                managerT.isActive
            FROM
                data.Manager AS managerT
            INNER JOIN
                data.Person AS personT
                ON personT.jobCode = managerT.jobCode
            WHERE
                managerT.id = $id;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the manager's data using his unique key.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the manager list.
     */
    public function getManagerByKey($jobCode)
    {
        $statement = "
            SELECT
                managerT.id,
                managerT.jobCode,
                firstSurname,
                secondSurname,
                firstName,
                managerT.isActive
            FROM
                data.Manager AS managerT
            INNER JOIN
                data.Person AS personT
                ON personT.jobCode = managerT.jobCode
            WHERE
                managerT.jobCode = '$jobCode';
        ";

        return DatabaseConnection::dqlStatement($statement);
    }
}
