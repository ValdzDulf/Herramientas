<?php

namespace core\models;

use core\exceptions\DatabaseException;

use mysqli_result;

/**
 * Describes the business rules for the construction of the navigation menu.
 *
 * @package   core\models
 *
 * @author    Diego Valentín
 * @copyright 2022 - Management Information System
 *
 * @version   1.0.0
 * @since     1.0.0 First time this was introduced.
 */
class MenuModel
{
    /**
     * Fetches the list of options that build the navigation menu according to the user's profile.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $profileId  User role assigned to the employee.
     *
     * @return mysqli_result Result set obtained from a query.
     */
    public function getOptionsList($profileId)
    {
        $statement = "
            SELECT
                listOptions
            FROM
                data.Menu
            WHERE
                profileId = $profileId
                AND isActive = 1;
        ";

        return DatabaseConnection::dqlResourceStatement($statement);
    }

    /**
     * Fetches the properties of the menu option.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  string  $optionName  Name of option.
     *
     * @return mysqli_result Result set obtained from a query.
     */
    public function getPropertiesOption($optionName)
    {
        $statement = "
            SELECT
                id,
                descriptiveName,
                associatedModule,
                icon,
                IF (associatedModule != '#', '', 'dropdown') AS dataToggle,
                IF (associatedModule != '#', '', 'dropdown-toggle') AS dropdownToggle
            FROM
                data.MenuOption
            WHERE
                descriptiveName = '$optionName'
                AND isActive = 1;
        ";

        return DatabaseConnection::dqlResourceStatement($statement);
    }

    /**
     * Fetches the options for the submenu.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $optionId  Unique option identifier.
     *
     * @return mysqli_result Result set obtained from a query.
     */
    public function getSubMenuOptions($optionId)
    {
        $statement = "
            SELECT
                optionId,
                moduleName,
                descriptiveName,
                icon
            FROM
                data.SubMenu
            WHERE
                optionId = $optionId
                AND isActive = 1;
        ";

        return DatabaseConnection::dqlResourceStatement($statement);
    }
}
