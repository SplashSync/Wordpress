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

/**
 * WooCommerce Order Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // Customer Object
        $this->fieldsFactory()->Create((string) self::objects()->Encode("ThirdParty", SPL_T_ID))
            ->Identifier("_customer_id")
            ->Name(__("Customer"))
            ->isRequired();
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/Invoice", "customer");
        } else {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/Organization", "ID");
        }

        //====================================================================//
        // Reference
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("reference")
            ->Name(__("Reference"))
            ->MicroData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
            ->isListed();
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/Invoice", "confirmationNumber");
        } else {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/Order", "orderNumber");
        }

        //====================================================================//
        // Order Date
        $this->fieldsFactory()->Create(SPL_T_DATE)
            ->Identifier("_date_created")
            ->Name(__("Order date"))
            ->MicroData("http://schema.org/Order", "orderDate")
            ->isRequired();
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
    protected function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_customer_id':
                if (!$this->object->get_customer_id()) {
                    $this->out[$fieldName] = null;

                    break;
                }
                $this->out[$fieldName] = self::objects()
                    ->encode("ThirdParty", (string) $this->object->get_customer_id())
                ;

                break;
            case 'reference':
                $this->out[$fieldName] = "#".$this->object->get_order_number();

                break;
            case '_date_created':
                $orderDate = $this->object->get_date_created();
                $this->out[$fieldName] = is_null($orderDate) ? null : $orderDate->format(SPL_T_DATECAST);

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
     */
    protected function setCoreFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case '_customer_id':
                $this->setGeneric($fieldName, self::objects()->id((string) $fieldData));

                break;
            case '_date_created':
                $this->setGeneric($fieldName, (string) $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
