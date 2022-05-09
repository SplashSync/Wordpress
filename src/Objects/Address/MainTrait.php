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
            ->Identifier("company")
            ->Name(__("Company"))
            ->MicroData("http://schema.org/Organization", "legalName");

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
        // Addess
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("address_1")
            ->Name(__("Address line 1"))
            ->isLogged()
            ->MicroData("http://schema.org/PostalAddress", "streetAddress");

        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("postcode")
            ->Name(__("Postcode / ZIP"))
            ->MicroData("http://schema.org/PostalAddress", "postalCode")
            ->isLogged()
            ->isListed();

        //====================================================================//
        // City Name
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("city")
            ->Name(__("City"))
            ->MicroData("http://schema.org/PostalAddress", "addressLocality")
            ->isListed();

        //====================================================================//
        // Country Name
//        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//                ->Identifier("country")
//                ->Name($langs->trans("CompanyCountry"))
//                ->isReadOnly()
//                ->Group($GroupName)
//                ->isListed();

        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->Create(SPL_T_COUNTRY)
            ->Identifier("country")
            ->Name(__("Country"))
            ->isLogged()
            ->MicroData("http://schema.org/PostalAddress", "addressCountry");

        //====================================================================//
        // State code
        $this->fieldsFactory()->Create(SPL_T_STATE)
            ->Identifier("state")
            ->Name(__("State / County"))
            ->MicroData("http://schema.org/PostalAddress", "addressRegion")
            ->isNotTested();

        //====================================================================//
        // Phone Pro
        $this->fieldsFactory()->Create(SPL_T_PHONE)
            ->Identifier("phone")
            ->Name(__("Phone"))
            ->MicroData("http://schema.org/Person", "telephone")
            ->isLogged()
            ->isListed();

        //====================================================================//
        // Email
        $this->fieldsFactory()->Create(SPL_T_EMAIL)
            ->Identifier("email")
            ->Name(__("Email address"))
            ->MicroData("http://schema.org/ContactPoint", "email")
            ->isLogged()
            ->isListed();
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
            case 'postcode':
            case 'city':
            case 'country':
            case 'state':
                /** @var scalar $metaData */
                $metaData = get_user_meta($this->object->ID, $this->encodeFieldId($fieldName), true);
                $this->out[$fieldName] = $metaData;

                break;
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
}
