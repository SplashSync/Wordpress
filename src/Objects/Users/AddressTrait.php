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
        // Customer Fullname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("company_safe")
            ->Name("Customer Fullname")
            ->isReadOnly();

        //====================================================================//
        // Company
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("company")
            ->Name(__("Company"))
            ->MicroData("http://schema.org/Organization", "alternateName")
            ->isReadOnly();

        //====================================================================//
        // Addess
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("address_1")
            ->Name(__("Address line 1"))
            ->MicroData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly();

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("postcode")
            ->Name(__("Postcode / ZIP"))
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->isReadOnly();

        //====================================================================//
        // City Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("city")
            ->Name(__("City"))
            ->MicroData("http://schema.org/PostalAddress", "addressLocality")
            ->isReadOnly();

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->Create(SPL_T_COUNTRY)
            ->Identifier("country")
            ->Name(__("Country"))
            ->MicroData("http://schema.org/PostalAddress", "addressCountry")
            ->isReadOnly();

        //====================================================================//
        // State code
        $this->fieldsFactory()->Create(SPL_T_STATE)
            ->Identifier("state")
            ->Name(__("State / County"))
            ->MicroData("http://schema.org/PostalAddress", "addressRegion")
            ->isReadOnly();

        //====================================================================//
        // Phone Pro
        $this->fieldsFactory()->Create(SPL_T_PHONE)
            ->Identifier("phone")
            ->Name(__("Phone"))
            ->MicroData("http://schema.org/Person", "telephone")
            ->isReadOnly();
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
    private function getAddressFields($key, $fieldName)
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
    private function getCompanySafeFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'company_safe':
                $company = get_user_meta($this->object->ID, "billing_company", true);
                $this->out[$fieldName] = empty($company) ? $this->object->user_login : $company;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
