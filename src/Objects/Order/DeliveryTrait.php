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

namespace Splash\Local\Objects\Order;

use Address;
use Country;
use Splash\Client\Splash;
use State;

/**
 * ReadOnly Access to Order Delivery Address Fields
 */
trait DeliveryTrait
{
    /**
     * @var string
     */
    private static string $groupName = "Delivery";

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDeliveryFields(): void
    {
        //====================================================================//
        // Company
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_company")
            ->name(__("Company"))
            ->microData("http://schema.org/Organization", "legalName")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("formatted_shipping_full_name")
            ->name("Contact Name")
            ->microData("http://schema.org/PostalAddress", "alternateName")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_address_1")
            ->name(self::$groupName)
            ->microData("http://schema.org/PostalAddress", "streetAddress")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Address Complement
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_address_2")
            ->name(self::$groupName." (2)")
            ->group(self::$groupName)
            ->microData("http://schema.org/PostalAddress", "postOfficeBoxNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Zip Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_postcode")
            ->name("Zip/Postal Code")
            ->microData("http://schema.org/PostalAddress", "postalCode")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // City Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_city")
            ->name(__("City"))
            ->microData("http://schema.org/PostalAddress", "addressLocality")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // State Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_state")
            ->name(__("State"))
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Other
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("customer_note")
            ->name("Other")
            ->description("Other: Remarks, Relay Point Code, more...")
            ->MicroData("http://schema.org/PostalAddress", "description")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
    }

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDeliveryPart2Fields()
    {
        //====================================================================//
        // Country ISO Code
        $this->fieldsFactory()->create(SPL_T_COUNTRY)
            ->identifier("shipping_country")
            ->name(__("Country"))
            ->microData("http://schema.org/PostalAddress", "addressCountry")
            ->group(self::$groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Phone
        $this->fieldsFactory()->create(SPL_T_PHONE)
            ->identifier("shipping_phone")
            ->group(self::$groupName)
            ->name(__("Home phone"))
            ->microData("http://schema.org/PostalAddress", "telephone")
            ->isIndexed()
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getDeliverySimpleFields(string $key, string $fieldName): void
    {
        $addressFields = array(
            'shipping_company', 'formatted_shipping_full_name',
            'shipping_address_1', 'shipping_address_2',
            'shipping_postcode', 'shipping_city', 'shipping_state', 'shipping_country',
            'customer_note', 'shipping_phone'
        );
        //====================================================================//
        // READ Fields
        if (in_array($fieldName, $addressFields, true)) {
            $methodName = 'get_'.$fieldName;
            if (method_exists($this->object, $methodName)) {
                $this->out[$fieldName] = $this->object->{$methodName}();
            } else {
                Splash::log()->dump(get_class_methods($this->object));
                $this->out[$fieldName] = null;
            }

            unset($this->in[$key]);
        }
    }
}
