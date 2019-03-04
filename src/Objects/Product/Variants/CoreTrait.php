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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;
use WC_Product;
use WP_Post;

/**
 * Prestashop Product Variant Core Data Access
 */
trait CoreTrait
{
    /**
     * @var WP_Post
     */
    protected $baseObject;

    /**
     * @var WC_Product
     */
    protected $baseProduct;

    /**
     * Decide which IDs needs to be commited
     *
     * @param int $postId
     *
     * @return array|int
     */
    public static function getIdsForCommit($postId)
    {
        $childrens = self::isBaseProduct($postId);
        if ($childrens) {
            rsort($childrens);

            return $childrens;
        }

        return $postId;
    }

    /**
     * Load WooCommerce Parent Product
     *
     * @return bool
     */
    public function loadParent()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check if Product is Variant Product
        if (!$this->isVariantsProduct()) {
            return true;
        }
        $parentId = $this->product->get_parent_id();
        //====================================================================//
        // Prevent Commit for Parent Product
        $this->lock($parentId);
        //====================================================================//
        // Load WooCommerce Parent Product Object
        $product = wc_get_product($parentId);
        $post = get_post($parentId);
        if (is_wp_error($product) || is_wp_error($post)) {
            return Splash::log()->errTrace("Unable to load Parent Product (".$parentId.").");
        }

        if (($product) && ($post instanceof WP_Post)) {
            $this->baseProduct = $product;
            $this->baseObject = $post;
        }

        return true;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Check if Current product is Variant of Base Product
     *
     * @return bool
     */
    protected function isVariantsProduct()
    {
        return !empty($this->product->get_parent_id());
    }

    /**
     * Check if Given Product ID is Base Product of Variants
     *
     * @param mixed $postId
     *
     * @return array|false False or Array of Childrens Ids
     */
    protected static function isBaseProduct($postId)
    {
        $childrens = get_children(array(
            'post_type' => "product_variation",
            'post_parent' => $postId,
        ));
        if (sizeof($childrens) > 0) {
            return array_keys($childrens);
        }

        return false;
    }

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     */
    private function buildVariantsCoreFields()
    {
        //====================================================================//
        // Product Type Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("type")
            ->Name('Product Type')
            ->Group("Meta")
            ->addChoices(array("simple" => "Simple", "variant" => "Variant"))
            ->MicroData("http://schema.org/Product", "type")
            ->isReadOnly();

        //====================================================================//
        // Is Default Product Variant
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->Identifier("default_on")
            ->Name('Is default variant')
            ->Group("Meta")
            ->MicroData("http://schema.org/Product", "isDefaultVariation")
            ->isReadOnly();

        //====================================================================//
        // Default Product Variant
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->Identifier("default_id")
            ->Name('Default Variant')
            ->Group("Meta")
            ->MicroData("http://schema.org/Product", "DefaultVariation")
            ->isNotTested();

        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("parent_id")
            ->Name("Parent")
            ->Group("Meta")
            ->MicroData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getVariantsCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'parent_id':
                if ($this->isVariantsProduct()) {
                    $this->out[$fieldName] = (string) $this->product->get_parent_id();

                    break;
                }
                $this->out[$fieldName] = null;

                break;
            case 'type':
                if ($this->isVariantsProduct()) {
                    $this->out[$fieldName] = "variant";
                } else {
                    $this->out[$fieldName] = "simple";
                }

                break;
            case 'default_on':
                if ($this->isVariantsProduct()) {
                    $dfAttributes = $this->baseProduct->get_default_attributes();
                    $attributes = $this->product->get_attributes();
                    $this->out[$fieldName] = ($attributes == $dfAttributes);
                } else {
                    $this->out[$fieldName] = false;
                }

                break;
            case 'default_id':
                if ($this->isVariantsProduct()) {
                    $this->out[$fieldName] = $this->getDefaultVariantId();
                } else {
                    $this->out[$fieldName] = null;
                }

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     */
    private function setVariantsCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'default_on':
                break;
            case 'default_id':
                //====================================================================//
                // Load default Product
                $dfProduct = wc_get_product(self::objects()->id($fieldData));
                //====================================================================//
                // Check if Valid Data
                if (!$dfProduct) {
                    break;
                }
                //====================================================================//
                // Load Default Product Attributes
                $dfAttributes = $this->baseProduct->get_default_attributes();
                if ($dfAttributes == $dfProduct->get_attributes()) {
                    break;
                }
                //====================================================================//
                // Update Default Product Attributes
                $this->baseProduct->set_default_attributes($dfProduct->get_attributes());
                $this->baseProduct->save();

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Identify Default Variant Product Id
     *
     * @return null|string
     */
    private function getDefaultVariantId()
    {
        //====================================================================//
        // Not a Variable product => No default
        if (!$this->isVariantsProduct()) {
            return null;
        }
        //====================================================================//
        // No Children Products => No default
        $childrens = self::isBaseProduct($this->baseProduct->get_id());
        if (empty($childrens)) {
            return null;
        }
        //====================================================================//
        // Identify default in Children Products
        $dfAttributes = $this->baseProduct->get_default_attributes();
        foreach ($childrens as $children) {
            /** @var WC_Product $wcProduct */
            $wcProduct = wc_get_product($children);
            $attributes = $wcProduct->get_attributes();
            if ($dfAttributes == $attributes) {
                return (string) self::objects()->encode("Product", $children);
            }
        }

        return null;
    }
}
