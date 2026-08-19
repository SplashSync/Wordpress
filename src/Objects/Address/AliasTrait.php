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

use Splash\Local\Dictionary\AddressTypes;
use WC_Order;

/**
 * WordPress Users Address Alias Field
 */
trait AliasTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Alias Fields using FieldFactory
     *
     * @return void
     */
    protected function buildAliasFields()
    {
        //====================================================================//
        // Address Alias
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("alias")
            ->name(__("Alias"))
            ->microData("http://schema.org/PostalAddress", "name")
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
     */
    protected function getAliasFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (('alias' != $fieldName) || empty($this->addressType)) {
            return;
        }
        $this->out[$fieldName] = $this->getAddressAlias();

        unset($this->in[$key]);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Get Address Alias from Address Type
     *
     * Translated using Site Default Language: webservice requests
     * always run with the site locale.
     */
    private function getAddressAlias(): string
    {
        //====================================================================//
        // Build Alias from Address Type
        $alias = (in_array($this->addressType, array(AddressTypes::BILLING, AddressTypes::INVOICING), true))
            ? __("Billing address", "woocommerce")
            : __("Shipping address", "woocommerce");
        //====================================================================//
        // Order Addresses: prefix with Order Reference
        if ($this->object instanceof WC_Order) {
            $alias = sprintf(
                "%s #%s - %s",
                __("Order", "woocommerce"),
                $this->object->get_order_number(),
                $alias
            );
        }

        return $alias;
    }
}
