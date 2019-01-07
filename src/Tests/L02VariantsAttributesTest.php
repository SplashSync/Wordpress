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

namespace Splash\Tests;

use ArrayObject;
use Splash\Client\Splash;
use Splash\Local\Core\PluginManger;
use Splash\Local\Local;
use Splash\Local\Objects\Core\MultilangTrait;
use Splash\Tests\Tools\ObjectsCase;

/**
 * Local Objects Test Suite - Specific Verifications for Products Variants Attributes.
 */
class L02VariantsAttributesTest extends ObjectsCase
{
    use PluginManger;
    use MultilangTrait;
    
    /**
     * @var ArrayObject
     */
    protected $out;
    
    /**
     * Test Creation of a New Attribute Group
     *
     * @dataProvider sequencesProvider
     *
     * @param mixed $sequence
     */
    public function testCreateAttributeGroup($sequence)
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            return $this->markTestSkipped("WooCommerce Plugin is Not Active");
        }
        $this->loadLocalTestSequence($sequence);
        
        //====================================================================//
        //   Load Known Attribute Group
        $code   =   strtolower("CustomVariant");
        //====================================================================//
        // Detect Multilangual Mode
        if ($this->multilangMode() != self::$MULTILANG_DISABLED) {
            $name   =   self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" =>   3, "maxLength" =>   5));
        } else {
            $name   =   self::fakeFieldData(SPL_T_VARCHAR, null, array("minLength" =>   3, "maxLength" =>   5));
        }
        
        //====================================================================//
        //   Ensure Attribute Group is Deleted
        $this->ensureAttributeGroupIsDeleted($code);
        
        //====================================================================//
        //   Create a New Attribute Group
        $attributeGroupId   =   Splash::object("Product")->addAttributeGroup($code, $name);
        $attributeGroup     =   wc_get_attribute($attributeGroupId);
        
        //====================================================================//
        //   Verify Attribute Group
        $this->assertNotEmpty($attributeGroupId);
        $this->assertNotEmpty($attributeGroup->id);
        $this->assertEquals("pa_" . $code, $attributeGroup->slug);
        $this->assertEquals($name, $this->encodeMultilang($attributeGroup->name));
        
        //====================================================================//
        //   Verify Attributes Group Identification
        $this->assertEquals(
            $attributeGroup->id,
            Splash::object("Product")->getAttributeGroupByCode($code)
        );
        
        //====================================================================//
        //   Create a New Attribute Values
        for ($i=0; $i<5; $i++) {
            //====================================================================//
            // Detect Multilangual Mode
            if ($this->multilangMode() != self::$MULTILANG_DISABLED) {
                $value      =  self::fakeFieldData(SPL_T_MVARCHAR, null, array("minLength" => 5, "maxLength" => 10));
                $valueCode  =  strtolower($value["en_US"]);
            } else {
                $value      =  self::fakeFieldData(SPL_T_VARCHAR, null, array("minLength" => 5, "maxLength" => 10));
                $valueCode  =  strtolower($value);
            }
            //====================================================================//
            //   Verify Attributes Value Identification
            $this->assertFalse(
                Splash::object("Product")->getAttributeByCode($attributeGroup->slug, $valueCode)
            );
            $this->assertFalse(
                Splash::object("Product")->getAttributeByName($attributeGroup->slug, $value)
            );
            //====================================================================//
            //   Create Attribute Value
            $attributeId =  Splash::object("Product")
                ->addAttributeValue($attributeGroup->slug, $value);
            $this->assertNotEmpty($attributeId);
            $attribute  =   get_term($attributeId);
            $this->assertNotEmpty($attribute->term_id);
            $this->assertContains($this->decodeMultilang($value), $attribute->name);
           
            //====================================================================//
            //   Verify Attributes Value Identification
            if ($this->multilangMode() != self::$MULTILANG_WPMU) {
                $this->assertEquals(
                    $attribute->term_id,
                    Splash::object("Product")->getAttributeByCode(wc_attribute_taxonomy_name($code), $valueCode)
                );
            }
            $this->assertEquals(
                $attribute->term_id,
                Splash::object("Product")->getAttributeByName(wc_attribute_taxonomy_name($code), $value)
            );
        }
    }
    
    /**
     * Test Identification of An Attribute Group
     *
     * @return void
     */
    public function testIdentifyAttributeGroup()
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");

            return;
        }
        
        //====================================================================//
        //   Load Known Attribute Group
        $attributeGroupId   =   Splash::object("Product")->getAttributeGroupByCode("CustomVariant");
        $attributeGroup     =   wc_get_attribute($attributeGroupId);
        $this->assertNotEmpty($attributeGroupId);
        $this->assertContains("pa_", $attributeGroup->slug);
        $this->assertContains(strtolower("CustomVariant"), $attributeGroup->slug);
        //====================================================================//
        //   Load UnKnown Attribute Group
        $unknownGroupId     =   Splash::object("Product")->getAttributeGroupByCode(base64_encode(uniqid()));
        $this->assertFalse($unknownGroupId);
    }
    
    /**
     * {@inheritdoc}
     */
    public function sequencesProvider()
    {
        $result =   array();
        self::setUp();
        //====================================================================//
        // Check if Local Tests Sequences are defined
        if (method_exists(Splash::local(), "TestSequences")) {
            foreach (Splash::local()->testSequences("List") as $sequence) {
                $result[]   =   array($sequence);
            }
        } else {
            $result[]   =   array( 1 => "None");
        }

        return $result;
    }
    
    /**
     * Ensure Attribute Group Is Deleted
     *
     * @global array $wp_taxonomies
     *
     * @param string $code
     *
     * @SuppressWarnings(PHPMD.CamelCaseVariableName)
     */
    private function ensureAttributeGroupIsDeleted($code)
    {
        global $wp_taxonomies;
        
        //====================================================================//
        //   Load Known Attribute Group
        $attributeGroupId   =   Splash::object("Product")->getAttributeGroupByCode($code);
        //====================================================================//
        //   Delete Attribute Group
        if ($attributeGroupId) {
            wc_delete_attribute($attributeGroupId);
//            clean_taxonomy_cache(wc_attribute_taxonomy_name($Code));
            unset($wp_taxonomies[wc_attribute_taxonomy_name($code)]);
        }
        //====================================================================//
        //   Load Known Attribute Group
        $deletedGroupId   =   Splash::object("Product")->getAttributeGroupByCode($code);
        $this->assertFalse($deletedGroupId);
    }
}
