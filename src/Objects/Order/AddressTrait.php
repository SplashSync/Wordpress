<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Order;

use Splash\Local\Objects\Address;

/**
 * WooCommerce Order Address Fields Access
 */
trait AddressTrait
{
    /**
     * Build Address Fields using FieldFactory
     */
    private function buildAddressFields()
    {
        //====================================================================//
        // Billing Address
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Address", SPL_T_ID))
            ->Identifier("billing_address_id")
            ->Name(__('Billing details'))
            ->MicroData("http://schema.org/Order", "billingAddress")
            ->isReadOnly();
        
        //====================================================================//
        // Shipping Address
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Address", SPL_T_ID))
            ->Identifier("shipping_address_id")
            ->Name(__('Shipping details'))
            ->MicroData("http://schema.org/Order", "orderDelivery")
            ->isReadOnly();
    }
    
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
            //====================================================================//
            // Billing/Shipping Address Object Id Readings
            case 'billing_address_id':
            case 'shipping_address_id':
                $customerId = $this->object->get_customer_id();
                if (!$customerId) {
                    $this->out[$fieldName] = null;

                    break;
                }
                if ("billing_address_id" == $fieldName) {
                    $this->out[$fieldName] = self::objects()->Encode("Address", Address::encodeBillingId($customerId));
                } else {
                    $this->out[$fieldName] = self::objects()->Encode("Address", Address::encodeDeliveryId($customerId));
                }

                break;
            default:
                return;
        }
        
        unset($this->in[$key]);
    }
}
