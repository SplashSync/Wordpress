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

use Splash\Local\Objects\Address;

/**
 * WooCommerce Order Address Fields Access
 */
trait AddressTrait
{
    /**
     * Build Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAddressFields(): void
    {
        //====================================================================//
        // Billing Address ID
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("billing_address_id")
            ->name(__('Billing details'))
            ->microData("http://schema.org/Order", "billingAddress")
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Address ID
        $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
            ->identifier("shipping_address_id")
            ->name(__('Shipping details'))
            ->microData("http://schema.org/Order", "orderDelivery")
            ->isReadOnly()
        ;
        //====================================================================//
        // Billing Address as String
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("billing")
            ->name('Billing Address')
            ->isReadOnly()
        ;
        //====================================================================//
        // Shipping Address as String
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping")
            ->name('Shipping Address')
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
    protected function getAddressFields(string $key, string $fieldName): void
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
                    $this->out[$fieldName] = self::objects()
                        ->encode("Address", Address::encodeBillingId((string) $customerId));
                } else {
                    $this->out[$fieldName] = self::objects()
                        ->encode("Address", Address::encodeDeliveryId((string) $customerId));
                }

                break;
                //====================================================================//
                // Billing Address as String
            case 'billing':
                $this->out[$fieldName] = $this->object->get_formatted_billing_address();

                break;
                //====================================================================//
                // Shipping Address as String
            case 'shipping':
                $this->out[$fieldName] = $this->object->get_formatted_shipping_address();

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
