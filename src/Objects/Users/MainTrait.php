<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Users;

/**
 * Wordpress Users Main Data Access
 */
trait MainTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Main Fields using FieldFactory
     *
     * @return void
     */
    private function buildMainFields()
    {
        //====================================================================//
        // User Login
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("user_login")
            ->Name(__("Username"))
            ->MicroData("http://schema.org/Organization", "legalName")
            ->isNotTested();

        //====================================================================//
        // Firstname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("first_name")
            ->Name(__("First Name"))
            ->MicroData("http://schema.org/Person", "familyName")
            ->Association("first_name", "last_name")
            ->isListed();

        //====================================================================//
        // Lastname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("last_name")
            ->Name(__("Last Name"))
            ->MicroData("http://schema.org/Person", "givenName")
            ->Association("first_name", "last_name")
            ->isListed();

        //====================================================================//
        // WebSite
        $this->fieldsFactory()->Create(SPL_T_URL)
            ->Identifier("user_url")
            ->Name(__("Website"))
            ->MicroData("http://schema.org/Organization", "url");
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getMainFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'first_name':
            case 'last_name':
                $this->getUserMeta($fieldName);

                break;
            case 'user_login':
            case 'user_url':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setMainFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'first_name':
            case 'last_name':
                $this->setUserMeta($fieldName, $fieldData);

                break;
            case 'user_login':
            case 'user_url':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
