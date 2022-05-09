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

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore as Splash;
use Splash\Local\Local;

/**
 * Wholesale Prices for WooCommerce by Wholesale Data Access
 */
trait WholesalePricesTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildWholesalePriceFields(): void
    {
        //====================================================================//
        // Ensure Plugin is Active
        if (!Local::hasWooWholesalePrices()) {
            return;
        }
        //====================================================================//
        // Walk on Available Wholesale Prices
        foreach ($this->getWholesalePriceMeta() as $wholesalePriceName) {
            //====================================================================//
            // Product Wholesale Selling Price
            $this->fieldsFactory()->create(SPL_T_PRICE)
                ->identifier(strtolower($wholesalePriceName))
                ->name(ucwords(str_replace("_", " ", $wholesalePriceName)))
                ->microData("http://schema.org/Product", strtolower($wholesalePriceName))
            ;
        }
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
    protected function getWholesalePriceFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Ensure Plugin is Active
        if (!Local::hasWooWholesalePrices()) {
            return;
        }
        //====================================================================//
        // Walk on Available Wholesale Prices
        foreach ($this->getWholesalePriceMeta() as $wholesalePriceName) {
            if ($fieldName != strtolower($wholesalePriceName)) {
                continue;
            }
            //====================================================================//
            // Load General Infos
            $wcInclTax = wc_prices_include_tax();
            $taxRate = $this->getPriceBaseTaxRate();
            //====================================================================//
            // Read Wholesale Price
            /** @phpstan-ignore-next-line */
            $price = (double)  get_post_meta($this->object->ID, $fieldName, true);
            //====================================================================//
            // Build Price Array
            $this->out[$fieldName] = self::prices()->Encode(
                $wcInclTax ? null : $price,
                $taxRate,
                $wcInclTax ? $price :  null,
                get_woocommerce_currency(),
                get_woocommerce_currency_symbol()
            );

            unset($this->in[$key]);
        }
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setWholesalePriceFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // Ensure Plugin is Active
        if (!Local::hasWooWholesalePrices() || !is_array($fieldData)) {
            return;
        }
        //====================================================================//
        // Walk on Available Wholesale Prices
        foreach ($this->getWholesalePriceMeta() as $wholesalePriceName) {
            if ($fieldName != strtolower($wholesalePriceName)) {
                continue;
            }
            //====================================================================//
            // Write Wholesale Price
            $newPrice = wc_prices_include_tax()
                ? self::prices()->taxIncluded($fieldData)
                : self::prices()->taxExcluded($fieldData);
            $this->setPostMeta($fieldName, $newPrice);

            unset($this->in[$fieldName]);
        }
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Get List of Available Prices from Plugin
     *
     * @return array
     */
    private function getWholesalePriceMeta(): array
    {
        try {
            /** @phpstan-ignore-next-line */
            return \WooCommerceWholeSalePrices::getInstance()->wwp_import_export->wholesale_prices_meta();
        } catch (\Throwable $throwable) {
            Splash::log()->report($throwable);

            return array();
        }
    }
}
