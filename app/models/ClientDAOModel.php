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
class ClientDAOModel
{
    /**
     * Fetches the client's data using his unique id.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @param  integer  $id  Unique client identifier.
     *
     * @return array An associative array holding the client list.
     */
    public function getClientById($id)
    {
        $statement = "
            SELECT
                id,
                internalKey,
                descriptiveName,
                bundle,
                isActive
            FROM
                data.Client
            WHERE
                id = $id;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }

    /**
     * Fetches the clients available to a manager.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @param  integer  $managerId  Unique manager identifier.
     *
     * @return array An associative array holding the client list.
     */
    public function getClientsExcludingManager($managerId)
    {
        $statement = "
            SELECT
                id,
                internalKey,
                descriptiveName,
                bundle
            FROM
                data.Client AS clientT
            WHERE
                NOT EXISTS(
                    SELECT
                        id
                    FROM
                        data.ClientManager AS clientManagerT
                    WHERE
                        clientManagerT.clientId = clientT.id
                        AND clientManagerT.managerId = $managerId
                )
                AND clientT.isActive = 1;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }
}
