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

use Automattic\WooCommerce\Utilities\FeaturesUtil;
use Splash\Local\Local;

/**
 * WooCommerce Product Cost of Goods Data Access
 *
 * Exposed only when WooCommerce "Cost of Goods Sold" feature is enabled.
 * Paired with Splash Scopes via http://schema.org/Product => wholesalePrice.
 */
trait CostOfGoodsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Cost of Goods Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCostOfGoodsFields(): void
    {
        //====================================================================//
        // Ensure Cost of Goods Feature is Active
        if (!$this->isCostPriceFeatureActive()) {
            return;
        }
        //====================================================================//
        // Product Cost of Goods => Cost Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("_cogs_total_value")
            ->name(__("Cost of goods", "woocommerce"))
            ->description(__("Product")." : ".__("Cost of goods", "woocommerce"))
            ->microData("http://schema.org/Product", "wholesalePrice")
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
    protected function getCostOfGoodsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter Field Id
        if (('_cogs_total_value' != $fieldName) || !$this->isCostPriceFeatureActive()) {
            return;
        }
        //====================================================================//
        // Build Price Array (Cost Prices are Always Stored Tax Excluded)
        $this->out[$fieldName] = self::prices()->encode(
            $this->getCogsValue(),
            $this->getPriceBaseTaxRate(),
            null,
            get_woocommerce_currency(),
            get_woocommerce_currency_symbol()
        );

        unset($this->in[$key]);
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
    protected function setCostOfGoodsFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Filter Field Id
        if (('_cogs_total_value' != $fieldName) || !$this->isCostPriceFeatureActive()) {
            return;
        }
        //====================================================================//
        // Write Cost of Goods Value (Cost Prices are Always Tax Excluded)
        if (is_array($fieldData)) {
            $this->setPostMeta($fieldName, self::prices()->taxExcluded($fieldData));
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Read Product Cost of Goods Value, with fallback on Parent Value
     *
     * Wc Variations use Override Semantics: an empty Variation Value
     * falls back to Parent Product Value.
     */
    private function getCogsValue(): float
    {
        /** @var false|scalar $cogsValue */
        $cogsValue = get_post_meta($this->object->ID, "_cogs_total_value", true);
        //====================================================================//
        // Empty on Variation => Fallback to Parent Value
        if (("" === $cogsValue) && $this->object->post_parent) {
            /** @var false|scalar $cogsValue */
            $cogsValue = get_post_meta($this->object->post_parent, "_cogs_total_value", true);
        }

        return is_numeric($cogsValue) ? (double) $cogsValue : 0.0;
    }

    /**
     * Check if WooCommerce Cost of Goods Sold Feature is Active
     */
    private function isCostPriceFeatureActive(): bool
    {
        if (!Local::hasWooCommerce() || !class_exists(FeaturesUtil::class)) {
            return false;
        }

        return FeaturesUtil::feature_is_enabled("cost_of_goods_sold");
    }
}
