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

use WC_Order_Item;
use WC_Order_Item_Shipping;

/**
 * Access to Order Tracking Information
 */
trait TrackingTrait
{
    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildFirstTrackingFields(): void
    {
        //====================================================================//
        // Order Shipping Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_method_title")
            ->name("Shipping Method")
            ->microData("http://schema.org/ParcelDelivery", "provider")
            ->group("Tracking")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Shipping Method Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("shipping_method_id")
            ->name("Carrier Code")
            ->microData("http://schema.org/ParcelDelivery", "identifier")
            ->group("Tracking")
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
    protected function getTrackingFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            //====================================================================//
            // Generic Infos
            case 'shipping_method_id':
                //====================================================================//
                // Get Carrier Description
                $shippingMethod = $this->getOrderFirstShippingItem();
                $this->out[$fieldName] = $shippingMethod
                    ? ($shippingMethod->get_method_id() ?: "default")
                    : 'default'
                ;

                break;
            case 'shipping_method_title':
                //====================================================================//
                // Get Carrier Name
                $shippingMethod = $this->getOrderFirstShippingItem();
                $this->out[$fieldName] = $shippingMethod
                    ? $shippingMethod->get_method_title()
                    : 'default'
                ;

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    /**
     * Get Order First Shipping Item
     *
     * @return null|WC_Order_Item_Shipping
     */
    private function getOrderFirstShippingItem(): ?WC_Order_Item_Shipping
    {
        foreach ($this->object->get_items("shipping") as $item) {
            if ($item instanceof WC_Order_Item_Shipping) {
                return $item;
            }
        }

        return null;
    }
}
