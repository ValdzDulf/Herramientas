<?php

namespace core\controllers;

use core\exceptions\DatabaseException;

use core\models\MenuModel;

/**
 * Describes the business rules for the construction of the navigation menu.
 *
 * @package   core\controllers
 *
 * @author    Diego Valentín
 * @copyright 2022 - Management Information System
 *
 * @version   2.0.0
 * @since     2.0.0 Upgrade to Bootstrap version 5.
 * @since     1.0.0 First time this was introduced.
 */
class Menu
{
    /**
     * Builds the navigation menu from the user's profile.
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $profileId  User role assigned to the employee.
     *
     * @return void
     */
    public static function buildMenu($profileId)
    {
        $menuItem = '';

        $menuInstance = new MenuModel();

        $optionList = $menuInstance->getOptionsList($profileId);

        if ($optionList->num_rows >= 1) {
            $options = explode(',', $optionList->fetch_assoc()['listOptions']);

            foreach ($options as $option) {
                $propertiesOption = $menuInstance->getPropertiesOption($option)->fetch_assoc();

                $menuItem .= "
                        <li class='nav-item dropdown'>
                            <a href='{$propertiesOption['associatedModule']}'
                               class='nav-link {$propertiesOption['dropdownToggle']} text-white'
                               role='button' data-bs-toggle='{$propertiesOption['dataToggle']}'
                               aria-expanded='false'>
                                {$propertiesOption['icon']}{$propertiesOption['descriptiveName']}
                            </a>
                            <ul class='dropdown-menu'>";

                $subMenuOptions = $menuInstance->getSubMenuOptions(
                        $propertiesOption['id'])->fetch_all(MYSQLI_ASSOC);

                foreach ($subMenuOptions as $subOption) {
                    $menuItem .= "
                                <li>
                                    <a href='{$subOption['moduleName']}' class='dropdown-item'>
                                        {$subOption['icon']}{$subOption['descriptiveName']}
                                    </a>
                                </li>";
                }

                $menuItem .= "
                            </ul>
                        </li>";
            }

            View::set('menu', $menuItem);
        }

        # Clears explicitly the variables used.
        foreach (get_defined_vars() as $key => $var) {
            unset(${$key});
        }
    }

    /**
     * Builds the navigation menu from the user's profile (Bootstrap 4 version).
     *
     * @throws DatabaseException Will throw the exception if errors exist during a transaction with the
     *                           database.
     *
     * @param  integer  $profileId  User role assigned to the employee.
     *
     * @return void
     *
     * @deprecated It should not be used after changing all views to bootstrap 5.
     */
    public static function buildMenuOld($profileId)
    {
        $menuItem = '';

        $menuInstance = new MenuModel();

        $optionList = $menuInstance->getOptionsList($profileId);

        if ($optionList->num_rows >= 1) {
            $options = explode(',', $optionList->fetch_assoc()['listOptions']);

            foreach ($options as $option) {
                $propertiesOption = $menuInstance->getPropertiesOption($option)->fetch_assoc();

                $menuItem .= "
                        <li class='nav-item dropdown'>
                            <a href='{$propertiesOption['associatedModule']}'
                               class='nav-link {$propertiesOption['dropdownToggle']} text-white'
                               role='button' data-toggle='{$propertiesOption['dataToggle']}'
                               aria-expanded='false'>
                                {$propertiesOption['icon']}{$propertiesOption['descriptiveName']}
                            </a>
                            <div class='dropdown-menu'>";

                $subMenuOptions = $menuInstance->getSubMenuOptions(
                        $propertiesOption['id'])->fetch_all(MYSQLI_ASSOC);

                foreach ($subMenuOptions as $subOption) {
                    $menuItem .= "
                                <a href='{$subOption['moduleName']}' class='dropdown-item'>
                                    {$subOption['icon']}{$subOption['descriptiveName']}
                                </a>";
                }

                $menuItem .= "
                            </div>
                        </li>";
            }

            View::set('menu', $menuItem);
        }

        # Clears explicitly the variables used.
        foreach (get_defined_vars() as $key => $var) {
            unset(${$key});
        }
    }
}
