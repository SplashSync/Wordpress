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

namespace Splash\Local\Objects\Address;

/**
 * WordPress Users Address Main Data Access
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
        // Company
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("company")
            ->name(__("Company"))
            ->microData("http://schema.org/Organization", "legalName")
        ;
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("first_name")
            ->name(__("First Name"))
            ->microData("http://schema.org/Person", "familyName")
            ->association("first_name", "last_name")
            ->isListed()
        ;
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("last_name")
            ->name(__("Last Name"))
            ->microData("http://schema.org/Person", "givenName")
            ->association("first_name", "last_name")
            ->isListed()
        ;
        //====================================================================//
        // Address 1
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("address_1")
            ->name(__("Address line 1"))
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->isLogged()
        ;
        //====================================================================//
        // Address 2
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("address_2")
            ->name(__("Address line 2"))
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isLogged()
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
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("postcode")
            ->name(__("Postcode / ZIP"))
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->isLogged()
            ->isListed()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("city")
            ->name(__("City"))
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->isListed()
        ;
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->Create(SPL_T_COUNTRY)
            ->identifier("country")
            ->name(__("Country"))
            ->isLogged()
            ->microData("http://schema.org/PostalAddress", "addressCountry")
        ;
        //====================================================================//
        // State code
        $this->fieldsFactory()->Create(SPL_T_STATE)
            ->identifier("state")
            ->name(__("State / County"))
            ->microData("http://schema.org/PostalAddress", "addressRegion")
            ->isNotTested()
        ;
    }

    /**
     * Build Main Fields using FieldFactory
     *
     * @return void
     */
    protected function buildMainContactFields()
    {
        //====================================================================//
        // Phone Pro
        $this->fieldsFactory()->Create(SPL_T_PHONE)
            ->identifier("phone")
            ->name(__("Phone"))
            ->microData("http://schema.org/Person", "telephone")
            ->isLogged()
            ->isListed()
        ;
        //====================================================================//
        // Email
        $this->fieldsFactory()->Create(SPL_T_EMAIL)
            ->identifier("email")
            ->name(__("Email address"))
            ->microData("http://schema.org/ContactPoint", "email")
            ->isLogged()
            ->isListed()
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
    protected function getMainFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->addressType)) {
            return;
        }

        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'company':
            case 'first_name':
            case 'last_name':
            case 'address_1':
            case 'address_2':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
                /** @var scalar $metaData */
                $metaData = get_user_meta($this->object->ID, $this->encodeFieldId($fieldName), true);
                $this->out[$fieldName] = $metaData;

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
    protected function getMainContactFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->addressType)) {
            return;
        }
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'phone':
            case 'email':
                /** @var scalar $metaData */
                $metaData = get_user_meta($this->object->ID, $this->encodeFieldId($fieldName, self::$billing), true);
                $this->out[$fieldName] = $metaData;

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
     * @param scalar $fieldData Field Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setMainFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->addressType)) {
            return;
        }

        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'company':
            case 'first_name':
            case 'last_name':
            case 'address_1':
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
                $this->setUserMeta($this->encodeFieldId($fieldName), $fieldData);

                break;
            case 'phone':
            case 'email':
                $this->setUserMeta($this->encodeFieldId($fieldName, self::$billing), $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getMainExtraFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'address_full':
                /** @var false|scalar $address1 */
                $address1 = get_user_meta($this->object->ID, $this->encodeFieldId('address_1'), true);
                /** @var false|scalar $address2 */
                $address2 = get_user_meta($this->object->ID, $this->encodeFieldId('address_2'), true);
                $this->out[$fieldName] = $address1." ".$address2;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }
}
