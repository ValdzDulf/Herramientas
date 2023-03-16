<?php

namespace app\models;

use core\exceptions\DatabaseException;

use core\models\DatabaseConnection;

/**
 * Describes the business rules for accessing the profile's data
 *
 * @package   app\models
 *
 * @author    Diego Valentin
 * @copyright 2022  Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class ProfileDAOModel
{
    /**
     * Fetches the active profile's data.
     *
     * @throws DatabaseException  Will throw the exception if errors exist during a transaction with the
     *                            database.
     *
     * @return array An associative array holding the profile list.
     */
    public function getActiveProfiles()
    {
        $statement = "
            SELECT
                id,
                descriptiveName,
                explanation
            FROM
                data.Profile
            WHERE
                isActive = 1
            ORDER BY
                id;
        ";

        return DatabaseConnection::dqlStatement($statement);
    }
}
