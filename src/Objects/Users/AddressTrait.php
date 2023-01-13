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
            ->identifier("company_safe")
            ->name("Customer Fullname")
            ->isReadOnly()
        ;
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("company")
            ->name(__("Company"))
            ->isReadOnly()
        ;
        //====================================================================//
        // Address 1
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_1")
            ->name(__("Address line 1"))
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isReadOnly()
        ;
        //====================================================================//
        // Address 2
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_2")
            ->name(__("Address line 2"))
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Address Full
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_full")
            ->name(__("Address line 1 & 2"))
            ->microData("http://schema.org/PostalAddress", "alternateName")
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
            ->isIndexed()
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
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getAddressFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case "company":
            case 'address_1':
            case 'address_2':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
            case 'phone':
            case 'email':
                /** @var false|scalar $metadata */
                $metadata = get_user_meta($this->object->ID, "billing_".$fieldName, true);
                $this->out[$fieldName] = $metadata;

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
    private function getAddressExtraFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'company_safe':
                /** @var false|string $company */
                $company = get_user_meta($this->object->ID, "billing_company", true);
                $this->out[$fieldName] = empty($company) ? $this->object->user_login : $company;

                break;
            case 'address_full':
                /** @var false|scalar $address1 */
                $address1 = get_user_meta($this->object->ID, "billing_address_1", true);
                /** @var false|scalar $address2 */
                $address2 = get_user_meta($this->object->ID, "billing_address_2", true);
                $this->out[$fieldName] = $address1." ".$address2;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
