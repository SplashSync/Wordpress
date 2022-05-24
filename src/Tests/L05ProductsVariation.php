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

namespace Splash\Local\Tests;

use ArrayObject;
use Splash\Client\Splash;
use Splash\Models\Helpers\ObjectsHelper;
use Splash\Tests\WsObjects\O06SetTest;
use WC_Product;
use WC_Product_Variable;
use WP_Post;

/**
 * Wordpress Local Test Suite - Generate & Tests Dummy Variable Product & Variations
 *
 * @author SplashSync <contact@splashsync.com>
 */
class L05ProductsVariation extends O06SetTest
{
    const MAX_VARIATIONS = 3;
    const VARIABLE_PRODUCT = "PhpUnit-Product-Variable";
    const VARIATION_PRODUCT = "PhpUnit-Product-Varition";

    /**
     * @var WC_Product
     */
    private $variableProduct;

    /**
     * @var array
     */
    private $variations;

    /**
     * Setup System for testing
     */
    protected function setUp(): void
    {
        //====================================================================//
        // BOOT or REBOOT MODULE
        Splash::reboot();

        //====================================================================//
        // FAKE SPLASH SERVER HOST URL
        Splash::configuration()->WsHost = "No.Commit.allowed.not";

        //====================================================================//
        // Load Module Local Configuration (In Safe Mode)
        //====================================================================//
        $this->loadLocalTestParameters();

        //====================================================================//
        // Create Variable Product & Variations
        //====================================================================//

        //====================================================================//
        // Check or Create Product Test Attribute
        $product = $this->createVariableProduct();
        if ($product) {
            $this->variableProduct = $product;
        }

        //====================================================================//
        // Check or Create Product Test Attribute
        $this->variations = $this->createVariations();
    }

    //====================================================================//
    //   Functional Tests
    //====================================================================//

    /**
     * Verify Base product is Found for testing
     */
    public function testProductBase()
    {
        $this->assertNotEmpty($this->variableProduct);
        $this->assertInstanceOf("WC_Product_Variable", $this->variableProduct);

        $this->assertEquals(self::MAX_VARIATIONS, count($this->variableProduct->get_children()));
    }

    /**
     * Ensure Product Variations are Here for Testing
     */
    public function testProductVariations()
    {
        $this->assertNotEmpty($this->variations);
        $this->assertEquals(self::MAX_VARIATIONS, count($this->variations));
        foreach ($this->variations as $variation) {
            $this->assertInstanceOf("WC_Product_Variation", $variation);
        }
    }

    /**
     * Test Variables Products Links from Service
     */
    public function testVariableProductLinksFromService()
    {
        //====================================================================//
        //   Create Fields List
        $fields = array(
            "parent_id",
            "id@children",
            "sku@children",
            "attribute@children",
        );
        //====================================================================//
        //   Read Object Data
        $data = Splash::object("Product")
            ->get((string)$this->variableProduct->get_id(), $fields);

        //====================================================================//
        //   Verify Data
        $this->assertNotEmpty($data);
        $this->assertEmpty($data["parent_id"]);
        $this->assertNotEmpty($data["children"]);
        foreach ($this->variations as $variation) {
            $varData = array_shift($data["children"]);
            $this->assertNotEmpty($varData);
            $this->assertEquals(ObjectsHelper::encode("Product", (string)$variation->get_id()), $varData["id"]);
            $this->assertEquals($variation->get_sku(), $varData["sku"]);
            $this->assertEquals(implode(" | ", $variation->get_attributes()), $varData["attribute"]);
        }
    }

    /**
     * Test Variations Products Links from Service
     */
    public function testVariationProductLinksFromService()
    {
        //====================================================================//
        //   Create Fields List
        $fields = array(
            "parent_id",
            "id@children",
            "sku@children",
            "attribute@children",
        );

        foreach ($this->variations as $variation) {
            //====================================================================//
            //   Read Object Data
            $data = Splash::object("Product")->get((string)$variation->get_id(), $fields);

            //====================================================================//
            //   Verify Data
            $this->assertNotEmpty($data);

            $this->assertNotEmpty($data["parent_id"]);
            $this->assertEquals(
                ObjectsHelper::encode("Product", (string)$this->variableProduct->get_id()),
                $data["parent_id"]
            );

            $this->assertCount(0, $data["children"]);
        }
    }

    /**
     * Test Single variation Field from Module
     *
     * @dataProvider ObjectFieldsProvider
     *
     * @param string      $sequence
     * @param string      $objectType
     * @param ArrayObject $field
     */
    public function testSingleFieldFromModule(string $sequence, string $objectType, $field)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->variations as $prodVariation) {
            parent::testSetSingleFieldFromModule($sequence, $objectType, $field, $prodVariation->get_id());
        }
    }

    /**
     * Test Single variation Field from Service
     *
     * @dataProvider ObjectFieldsProvider
     *
     * @param string      $sequence
     * @param string      $objectType
     * @param ArrayObject $field
     */
    public function testSingleFieldFromService(string $sequence, string $objectType, $field)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->variations as $prodVariation) {
            parent::testSetSingleFieldFromService($sequence, $objectType, $field, $prodVariation->get_id());
        }
    }

    //====================================================================//
    //   Manage Variables Products
    //====================================================================//

    /**
     * Load a Variable Product
     *
     * @return null|false|WC_Product|WC_Product_Variable
     */
    public function loadVariableProduct()
    {
        //====================================================================//
        // Load From DataBase
        $posts = get_posts(array(
            'post_type' => "product",
            'post_status' => array_keys(get_post_statuses()),
            'title' => self::VARIABLE_PRODUCT,
        ));
        if (empty($posts)) {
            return null;
        }
        $post = array_shift($posts);

        return wc_get_product(($post instanceof WP_Post) ? $post->ID : $post);
    }

    /**
     * Create a Variable Product
     *
     * @return null|false|WC_Product
     */
    public function createVariableProduct()
    {
        $product = $this->loadVariableProduct();

        if (!empty($product)) {
            return $product;
        }

        $postId = wp_insert_post(array(
            "post_type" => "product",
            "post_title" => self::VARIABLE_PRODUCT,
        ));

        $variations = array();
        for ($i = 0; $i < self::MAX_VARIATIONS; $i++) {
            $variations[] = "Type-".$i;
        }

        if (is_integer($postId)) {
            wp_set_object_terms($postId, 'variable', 'product_type');
            update_post_meta($postId, "_product_attributes", array(
                "variant" => array(
                    "name" => "Variant",
                    "value" => implode(" | ", $variations),
                    "position" => 0,
                    "is_visible" => 1,
                    "is_variation" => 1,
                    "is_taxonomy" => 0,
                )
            ));
        }

        return wc_get_product($postId);
    }

    /**
     * Create a Product Variations
     *
     * @return array
     */
    public function createVariations(): array
    {
        $variations = array();
        $newChildrens = array();
        if (empty($this->variableProduct)) {
            return $variations;
        }

        $childrens = $this->variableProduct->get_children();

        for ($i = 0; $i < self::MAX_VARIATIONS; $i++) {
            //====================================================================//
            // Load Existing Product Variation
            $postId = array_shift($childrens);
            if (!empty($postId)) {
                $variations[] = wc_get_product($postId);
                $newChildrens[] = $postId;

                continue;
            }

            //====================================================================//
            // Create Product Variation
            $variationId = wp_insert_post(array(
                "post_type" => "product_variation",
                "post_title" => self::VARIATION_PRODUCT."-".$i,
                "post_name" => self::VARIATION_PRODUCT."-".$i,
                "post_parent" => $this->variableProduct->get_id(),
                "post_status" => "publish",
                "menu_order" => ($i + 1),
            ));

            if (is_int($variationId)) {
                update_post_meta($variationId, "attribute_variant", "Type-".$i);
            }

            $variations[] = wc_get_product($variationId);
            $newChildrens[] = $variationId;
        }

        if (serialize($childrens) != serialize($newChildrens)) {
            $this->variableProduct->set_children($newChildrens);
            $this->variableProduct->save();
        }

        return $variations;
    }

    //====================================================================//
    //   Data Provider Functions
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function objectFieldsProvider(): array
    {
        //====================================================================//
        //   Object & Fields Scope
        $objectType = "Product";
        $fields = array("_weight", "_height", "_length", "_width", "_stock", "_regular_price", "_thumbnail_id");

        $result = array();

        //====================================================================//
        // Check if Local Tests Sequences are defined
        if (method_exists(Splash::local(), "TestSequences")) {
            $sequences = Splash::local()->testSequences("List");
        } else {
            $sequences = array( 1 => "None");
        }

        //====================================================================//
        //   For Each Test Sequence
        foreach ($sequences as $sequence) {
            $this->loadLocalTestSequence($sequence);
            //====================================================================//
            //   Filter Object Fields
            $objectFields = Splash::object($objectType)->fields();
            $filteredFields = $this->filterFieldList($objectFields, $fields);
            foreach ($filteredFields as $filteredField) {
                $result[] = array($sequence, $objectType, $filteredField);
            }
        }

        return $result;
    }
}
