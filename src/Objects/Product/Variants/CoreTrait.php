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
use Splash\Local\Objects\Product;
use WC_Product;
use WC_Product_Variable;
use WP_Post;

/**
 * Product Variant Core Data Access
 */
trait CoreTrait
{
    /**
     * @var WP_Post
     */
    protected $baseObject;

    /**
     * @var null|WC_Product_Variable
     */
    protected $baseProduct;

    /**
     * Decide which IDs need to be committed
     *
     * @param int $postId
     *
     * @return array|int
     */
    public static function getIdsForCommit($postId)
    {
        $products = self::isBaseProduct($postId);
        if ($products) {
            //====================================================================//
            // Convert All Posts Ids to Master Posts Ids
            foreach ($products as &$children) {
                $children = Product::getMultiLangMaster($children);
            }
            rsort($products);

            return $products;
        }
        //====================================================================//
        // Convert Post Id to Master Post Id
        return Product::getMultiLangMaster($postId);
    }

    /**
     * Load WooCommerce Parent Product
     *
     * @return bool
     */
    public function loadParent(): bool
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
        $this->lock((string) $parentId);
        //====================================================================//
        // Load WooCommerce Parent Product Object
        $product = new WC_Product_Variable($parentId);
        $post = get_post($parentId);
        if (is_wp_error($product) || is_wp_error($post)) {
            return Splash::log()->errTrace("Unable to load Parent Product (".$parentId.").");
        }

        if ($post instanceof WP_Post) {
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
     * Get Base Product Id
     *
     * @return int
     */
    protected function getBaseProductId()
    {
        return $this->isVariantsProduct()
            ? $this->product->get_parent_id()
            : $this->product->get_id();
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
     *
     * @return void
     */
    private function buildVariantsCoreFields()
    {
        //====================================================================//
        // Product Type Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("type")
            ->name('Product Type')
            ->group("Meta")
            ->addChoices(array("simple" => "Simple", "variant" => "Variant"))
            ->microData("http://schema.org/Product", "type")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Default Product Variant
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("default_on")
            ->name('Is default variant')
            ->group("Meta")
            ->microData("http://schema.org/Product", "isDefaultVariation")
            ->isReadOnly()
        ;
        //====================================================================//
        // Default Product Variant
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("default_id")
            ->name('Default Variant')
            ->group("Meta")
            ->microData("http://schema.org/Product", "DefaultVariation")
            ->isNotTested()
        ;
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent_id")
            ->name("Parent")
            ->group("Meta")
            ->microData("http://schema.org/Product", "isVariationOf")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("parent_sku")
            ->name("Parent SKU")
            ->group("Meta")
            ->microData("http://schema.org/Product", "isVariationOfName")
            ->isIndexed()
            ->isNotTested()
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
    private function getVariantsCoreFields(string $key, string $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'type':
                if ($this->isVariantsProduct()) {
                    $this->out[$fieldName] = "variant";
                } else {
                    $this->out[$fieldName] = "simple";
                }

                break;
            case 'default_on':
                if ($this->isVariantsProduct() && isset($this->baseProduct)) {
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
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getVariantsParentFields(string $key, string $fieldName)
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
            case 'parent_sku':
                if ($this->isVariantsProduct()) {
                    // @phpstan-ignore-next-line
                    $this->out[$fieldName] = (string) get_post_meta(
                        $this->product->get_parent_id(),
                        "_sku",
                        true
                    ) ;

                    break;
                }
                $this->out[$fieldName] = null;

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
     *
     * @return void
     */
    private function setVariantsCoreFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'default_on':
                break;
            case 'default_id':
                //====================================================================//
                // Safety Check
                if (!is_scalar($fieldData)) {
                    break;
                }
                //====================================================================//
                // Load default Product
                $dfProduct = wc_get_product(self::objects()->id((string) $fieldData));
                //====================================================================//
                // Check if Valid Data
                if (!$dfProduct || !isset($this->baseProduct)) {
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
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setVariantsParentFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'parent_sku':
                //====================================================================//
                //  Simple Products >> Skipp Update
                if (!$this->isVariantsProduct()) {
                    break;
                }
                //====================================================================//
                //  Compare Field Data
                if (get_post_meta($this->product->get_parent_id(), "_sku", true) != $fieldData) {
                    update_post_meta($this->product->get_parent_id(), "_sku", $fieldData);
                    $this->needUpdate();
                }

                break;
            default:
                return;
        }
        unset($this->in[$fieldName]);
    }

    /**
     * Identify Default Variant Product ID
     *
     * @return null|string
     */
    private function getDefaultVariantId(): ?string
    {
        //====================================================================//
        // Not a Variable product => No default
        if (!$this->isVariantsProduct() || !isset($this->baseProduct)) {
            return null;
        }
        //====================================================================//
        // No Children Products => No default
        $children = self::isBaseProduct($this->baseProduct->get_id());
        if (empty($children)) {
            return null;
        }
        //====================================================================//
        // Identify default in Children Products
        $dfAttributes = $this->baseProduct->get_default_attributes();
        foreach ($children as $children) {
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
