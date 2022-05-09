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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;
use WC_Product;

/**
 * WooCommerce Product Variation Data Access
 */
trait VariantsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Variation Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariationFields()
    {
        //====================================================================//
        // CHILD PRODUCTS INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Product Variation List - Product Link
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->identifier("id")
            ->name(__("Children"))
            ->inList("variants")
            ->microData("http://schema.org/Product", "Variants")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation List - Product SKU
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->name(__("Variant SKU"))
            ->inList("variants")
            ->microData("http://schema.org/Product", "VariationName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Variation List - Variation Attribute
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->identifier("attribute")
            ->name(__("Attribute"))
            ->inList("variants")
            ->microData("http://schema.org/Product", "VariationAttribute")
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
    protected function getVariationsFields(string $key, string $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "variants", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Check if Product is Variant Product
        if (!$this->isVariantsProduct()) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // Load List of Product Variants
        $children = self::isBaseProduct($this->product->get_parent_id());
        if (false === $children) {
            unset($this->in[$key]);

            return;
        }
        //====================================================================//
        // READ Fields
        foreach ($children as $index => $productId) {
            //====================================================================//
            // SKIP Current Variant When in PhpUnit/Travis Mode
            // Only Existing Variant will be Returned
            if (!empty(Splash::input('SPLASH_TRAVIS')) && ($productId == $this->object->ID)) {
                continue;
            }
            //====================================================================//
            // Read requested Field
            $value = $this->getVariationsFieldValue($fieldId, $productId);

            self::lists()->Insert($this->out, "variants", $fieldId, $index, $value);
        }
        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function setVariationsFields(string $fieldName, $fieldData)
    {
        if ("variants" === $fieldName) {
            unset($this->in[$fieldName]);
        }
    }

    /**
     * Read requested Field
     *
     * @param string $fieldId   Field Identifier / Name
     * @param int    $productId Product Variant ID
     *
     * @return null|string
     */
    private function getVariationsFieldValue(string $fieldId, int $productId): ?string
    {
        //====================================================================//
        // Read requested Field
        switch ($fieldId) {
            case 'id':
                return (string) self::objects()->encode("Product", (string) $productId);
            case 'sku':
                // @phpstan-ignore-next-line
                return get_post_meta($productId, "_sku", true);
            case 'attribute':
                /** @var WC_Product $wcProduct */
                $wcProduct = wc_get_product($productId);

                return implode(" | ", $wcProduct->get_attributes());
        }

        return null;
    }
}
