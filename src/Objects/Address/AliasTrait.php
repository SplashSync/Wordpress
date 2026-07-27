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
     */
    private function getAddressAlias(): string
    {
        switch ($this->addressType) {
            case AddressTypes::BILLING:
                return __("Billing address", "woocommerce");
            case AddressTypes::DELIVERY:
                return __("Shipping address", "woocommerce");
            default:
                return __("Logistic address");
        }
    }
}
