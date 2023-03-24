<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for interaction with the client - manager relationship data.
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2023 Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ClientManagerModel
{
    /**
     * Updates the client manager relationship status.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $clientId   Unique client identifier.
     * @param  integer  $managerId  Unique manager identifier.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function updateStatus($clientId, $managerId)
    {
        $statement = "
            UPDATE
                data.ClientManager
            SET
                isActive = IF (isActive = 0, 1, 0)
            WHERE
                clientId = $clientId
                AND managerId = $managerId;
        ";

        return DatabaseConnection::dmlStatement($statement);
    }

    /**
     * Try to insert a client manager relationship.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $clientId   Unique client identifier.
     * @param  integer  $managerId  Unique manager identifier.
     *
     * @return int|string Number of affected rows, -1 indicates error.
     */
    public function insertClientManagerRelationship($clientId, $managerId)
    {
        $statement = "
            INSERT INTO data.ClientManager
                (clientId, managerId)
            VALUES
                ($clientId, $managerId);
        ";

        return DatabaseConnection::dmlStatement($statement);
    }
}
