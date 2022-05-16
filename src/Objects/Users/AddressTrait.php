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

use Splash\Local\Local;

/**
 * WooCommerce Customers Short Address Data Access
 */
trait AddressTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    private function buildAddressFields()
    {
        /**
         * Check if WooCommerce is active
         */
        if (!Local::hasWooCommerce()) {
            return;
        }
        //====================================================================//
        // Customer Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("full_name")
            ->name("[C] Customer Full Name")
            ->description("[ID] Company | Firstname + Lastname")
            ->microData("http://schema.org/Organization", "alternateName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Company Safe
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("company_safe")
            ->name("[C] ".__("Company Safe"))
            ->isReadOnly()
        ;
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("company")
            ->name(__("Company"))
            ->microData("http://schema.org/Organization", "name")
            ->isReadOnly()
        ;
        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_1")
            ->name(__("Address line 1"))
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("postcode")
            ->name(__("Postcode / ZIP"))
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("city")
            ->name(__("City"))
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->identifier("country")
            ->name(__("Country"))
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly()
        ;
        //====================================================================//
        // State code
        $this->fieldsFactory()->create(SPL_T_STATE)
            ->identifier("state")
            ->name(__("State / County"))
            ->microData("http://schema.org/PostalAddress", "addressRegion")
            ->isReadOnly()
        ;
        //====================================================================//
        // Phone Pro
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("phone")
            ->name(__("Phone"))
            ->microData("http://schema.org/Person", "telephone")
            ->isReadOnly()
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
    private function getAddressFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case "company":
            case 'address_1':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
            case 'phone':
            case 'email':
                $this->out[$fieldName] = get_user_meta($this->object->ID, "billing_".$fieldName, true);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getUserComputedFields(string $key, string $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'full_name':
                $company = get_user_meta($this->object->ID, "billing_company", true);
                $this->out[$fieldName] = empty($company) ? $this->object->user_login : $company;

                break;
            case 'company_safe':
                $company = get_user_meta($this->object->ID, "billing_company", true);
                $this->out[$fieldName] = !empty($company)
                    ? sprintf("[%s] %s", $this->object->id, $company)
                    : sprintf("[%s] %s %s", $this->object->id, $this->object->first_name, $this->object->last_name)
                ;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
