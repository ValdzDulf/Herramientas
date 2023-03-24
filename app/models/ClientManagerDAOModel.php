<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the client's data
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ClientManagerDAOModel
{
    /**
     * Fetches all client manager relationship, no matter their activation status.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the relationship list.
     */
    public function getAllRelationship()
    {
        $statement = "
            SELECT
                clientManagerT.clientId,
                clientT.internalKey AS client,
                clientManagerT.managerId,
                managerT.jobCode AS manager,
                clientManagerT.isActive
            FROM
                data.ClientManager AS clientManagerT
            INNER JOIN
                data.Client AS clientT
                ON clientManagerT.clientId = clientT.id
            INNER JOIN
                data.Manager AS managerT
                ON clientManagerT.managerId = managerT.id
            ORDER BY
                clientManagerT.clientId, clientManagerT.managerId;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }
}
