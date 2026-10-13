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

use PHPUnit\Framework\TestCase;
use Splash\Client\Splash;
use Splash\Local\Core\CustomFieldsManager as Manager;
use Splash\Local\Dictionary\CustomFields;
use Splash\Local\Local;

/**
 * Local Test Suite - Selection of Custom Fields Exposed to Splash
 */
class L02CustomFieldsSelectionTest extends TestCase
{
    /**
     * Prefix of Meta Keys Created for Tests
     */
    const PREFIX = "phpunit_cf_";

    /**
     * Test Parsing of Selection Rules
     *
     * @return void
     */
    public function testParseRules(): void
    {
        $this->assertSame(array(), Manager::parseRules(""));
        $this->assertSame(
            array("my_field", "acf_*", "!acf_tmp_*", "other"),
            Manager::parseRules(" my_field \r\n\n# Comment\nacf_*, !acf_tmp_*\nother\nmy_field\n")
        );
    }

    /**
     * Test Matching of Meta Keys against Patterns
     *
     * @return void
     */
    public function testIsMatching(): void
    {
        $this->assertTrue(Manager::isMatching("acf_color", array("acf_*")));
        $this->assertTrue(Manager::isMatching("product_gtin", array("*_gtin")));
        $this->assertTrue(Manager::isMatching("any.key (1)", array("*")));
        $this->assertTrue(Manager::isMatching("my_field", array("other", "my_field")));
        $this->assertFalse(Manager::isMatching("my_field_2", array("my_field")));
        $this->assertFalse(Manager::isMatching("xacf_color", array("acf_*")));
        $this->assertFalse(Manager::isMatching("acf.color", array("acf_.*")));
        $this->assertFalse(Manager::isMatching("my_field", array()));
    }

    /**
     * Test Selection of Meta Keys
     *
     * @return void
     */
    public function testSelect(): void
    {
        $metaKeys = array("acf_color", "acf_tmp_x", "gtin", "legacy", "session_tokens");
        //====================================================================//
        // No Rules => Unchanged, Keys Preserved
        $this->assertSame($metaKeys, Manager::select($metaKeys, array()));
        $this->assertFalse(Manager::hasSelection(array()));
        //====================================================================//
        // Exclusions Only => All but Excluded, still Limited
        $rules = array("!session_tokens", "!acf_tmp_*");
        $this->assertSame(
            array(0 => "acf_color", 2 => "gtin", 3 => "legacy"),
            Manager::select($metaKeys, $rules)
        );
        $this->assertFalse(Manager::hasSelection($rules));
        //====================================================================//
        // Selection => Only Selected, Declared Keys Added, Exclusions Win
        $rules = array("acf_*", "!acf_tmp_*", "gtin", "declared_key", "!declared_hidden", "declared_hidden");
        $this->assertSame(
            array("acf_color", "gtin", "declared_key"),
            array_values(Manager::select($metaKeys, $rules))
        );
        $this->assertTrue(Manager::hasSelection($rules));
        //====================================================================//
        // Select All
        $this->assertSame($metaKeys, array_values(Manager::select($metaKeys, array("*"))));
    }

    /**
     * Test Selection of Product Custom Fields in Object Schema
     *
     * @return void
     */
    public function testProductSchemaSelection(): void
    {
        if (!function_exists("wp_insert_post") || !Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");
        }
        //====================================================================//
        // Create a Product with more Custom Fields than the Limit
        $postId = wp_insert_post(array(
            "post_type" => "product",
            "post_title" => "PhpUnit Custom Fields",
            "post_status" => "publish",
        ));
        $this->assertIsInt($postId);
        $this->assertGreaterThan(0, $postId);
        $count = CustomFields::MAX_FIELDS + 10;
        for ($index = 0; $index < $count; $index++) {
            update_post_meta($postId, sprintf("%s%03d", self::PREFIX, $index), "Value ".$index);
        }
        $enabled = get_option("splash_cf_product");
        update_option("splash_cf_product", "1");

        try {
            //====================================================================//
            // Without Selection => Limited, with a Warning
            delete_option("splash_cf_product_fields");
            Splash::log()->cleanLog();
            $fieldIds = $this->getProductCustomFields();
            $this->assertLessThan(CustomFields::MAX_FIELDS, count($fieldIds));
            $this->assertStringContainsString("custom fields not exposed", implode(" ", Splash::log()->war));
            //====================================================================//
            // With Selection => All Selected, no Limit, no Warning
            update_option(
                "splash_cf_product_fields",
                self::PREFIX."*\n!".self::PREFIX."005\n".self::PREFIX."declared"
            );
            Splash::log()->cleanLog();
            $fieldIds = $this->getProductCustomFields();
            $this->assertCount($count, $fieldIds);
            $this->assertContains("custom_".self::PREFIX."000", $fieldIds);
            $this->assertContains("custom_".self::PREFIX."declared", $fieldIds);
            $this->assertNotContains("custom_".self::PREFIX."005", $fieldIds);
            $this->assertStringNotContainsString("custom fields not exposed", implode(" ", Splash::log()->war));
            //====================================================================//
            // Selection Filter => Completes Settings Rules
            $filter = function (array $rules, string $objectType): array {
                if ("product" == $objectType) {
                    $rules[] = "!".self::PREFIX."0*";
                }

                return $rules;
            };
            add_filter(Manager::FILTER, $filter, 10, 2);
            $fieldIds = $this->getProductCustomFields();
            remove_filter(Manager::FILTER, $filter, 10);
            $this->assertNotContains("custom_".self::PREFIX."000", $fieldIds);
            $this->assertContains("custom_".self::PREFIX."100", $fieldIds);
        } finally {
            //====================================================================//
            // Restore Settings & Delete Test Product
            delete_option("splash_cf_product_fields");
            if (false === $enabled) {
                delete_option("splash_cf_product");
            } else {
                update_option("splash_cf_product", $enabled);
            }
            wp_delete_post($postId, true);
        }
    }

    /**
     * Get Product Custom Fields Created for Tests
     *
     * @return string[]
     */
    private function getProductCustomFields(): array
    {
        $fieldIds = array_column((array) Splash::object("Product")->fields(), "id");

        return array_values(array_filter($fieldIds, function (string $fieldId): bool {
            return 0 === strpos($fieldId, "custom_".self::PREFIX);
        }));
    }
}
