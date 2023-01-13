<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
 * WordPress Users Main Data Access
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
    protected function buildMainFields()
    {
        //====================================================================//
        // User Login
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("user_login")
            ->name(__("Username"))
            ->microData("http://schema.org/Organization", "legalName")
            ->isIndexed()
            ->isNotTested()
        ;
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("first_name")
            ->name(__("First Name"))
            ->microData("http://schema.org/Person", "familyName")
            ->association("first_name", "last_name")
            ->isListed()
        ;
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("last_name")
            ->name(__("Last Name"))
            ->microData("http://schema.org/Person", "givenName")
            ->association("first_name", "last_name")
            ->isListed()
        ;
        //====================================================================//
        // Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("full_name")
            ->name("[C] Full Name")
            ->description("Company | Firstname + Lastname")
            ->microData("http://schema.org/Organization", "alternateName")
            ->isReadOnly()
        ;
        //====================================================================//
        // WebSite
        $this->fieldsFactory()->create(SPL_T_URL)
            ->identifier("user_url")
            ->name(__("Website"))
            ->microData("http://schema.org/Organization", "url")
        ;
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
    protected function getMainFields(string $key, string $fieldName): void
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
            case 'full_name':
                /** @var false|scalar $company */
                $company = get_user_meta($this->object->ID, "billing_company", true);
                $this->out[$fieldName] = !empty($company)
                    ? sprintf("%s [%s]", $company, $this->object->ID)
                    : sprintf(
                        "%s %s [%s]",
                        $this->object->last_name ?: $this->object->user_login,
                        $this->object->first_name,
                        $this->object->ID
                    )
                ;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setMainFields(string $fieldName, $fieldData): void
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
