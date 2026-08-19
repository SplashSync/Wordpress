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

use Splash\Local\Core\AddressesManager;
use Splash\Local\Dictionary\AddressTypes;

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
        if (AddressesManager::isActive(AddressTypes::BILLING)) {
            $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
                ->identifier("billing_address_id")
                ->name(__('Billing details'))
                ->microData("http://schema.org/Order", "billingAddress")
                ->isReadOnly()
            ;
        }
        //====================================================================//
        // Shipping Address ID
        if (AddressesManager::isActive(AddressTypes::DELIVERY)) {
            $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
                ->identifier("shipping_address_id")
                ->name(__('Shipping details'))
                ->microData("http://schema.org/Order", "orderDelivery")
                ->isReadOnly()
            ;
        }
        //====================================================================//
        // Logistic Address ID
        if (AddressesManager::isActive(AddressTypes::LOGISTIC)) {
            $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
                ->identifier("logistic_address_id")
                ->name(__('Logistic address'))
                ->microData("http://schema.org/Order", "deliveryAddress")
                ->isReadOnly()
            ;
        }
        //====================================================================//
        // Invoicing Address ID
        if (AddressesManager::isActive(AddressTypes::INVOICING)) {
            $this->fieldsFactory()->create((string) self::objects()->encode("Address", SPL_T_ID))
                ->identifier("invoicing_address_id")
                ->name(__('Invoicing address', 'splash-wordpress-plugin'))
                ->microData("http://schema.org/Order", "invoicingAddress")
                ->isReadOnly()
            ;
        }
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

    /**
     * Read requested Address Object Id Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getAddressIdsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Detect Address Type & Host Object Id
        switch ($fieldName) {
            case 'billing_address_id':
                $addressType = AddressTypes::BILLING;
                $hostId = $this->object->get_customer_id();

                break;
            case 'shipping_address_id':
                $addressType = AddressTypes::DELIVERY;
                $hostId = $this->object->get_customer_id();

                break;
            case 'logistic_address_id':
                $addressType = AddressTypes::LOGISTIC;
                $hostId = $this->object->get_id();

                break;
            case 'invoicing_address_id':
                $addressType = AddressTypes::INVOICING;
                $hostId = $this->object->get_id();

                break;
            default:
                return;
        }
        //====================================================================//
        // Encode Address Object Id (Empty if Type Disabled)
        $this->out[$fieldName] = ($hostId && AddressesManager::isActive($addressType))
            ? self::objects()->encode("Address", AddressTypes::encode($addressType, (string) $hostId))
            : null
        ;

        unset($this->in[$key]);
    }
}
