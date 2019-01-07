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
use Splash\Local\Local;
use Splash\Local\Objects\Core\MultilangTrait;
use Splash\Models\Objects\SimpleFieldsTrait;
use Splash\Tests\WsObjects\O06SetTest;

/**
 * Local Objects Test Suite - Specific Verifications for Products Variants.
 */
class L03VariantsCRUDTest extends O06SetTest
{
    use SimpleFieldsTrait;
    use MultilangTrait;
    
    /**
     * @var ArrayObject
     */
    protected $out;
    
    /**
     * @type    array
     */
    private $currentVariation;
        
    /**
     * @dataProvider objectFieldsProvider
     *
     * @param string $sequence
     * @param string $objectType
     * @param ArrayObject $field
     * @param null|string $forceObjectId
     */
    public function testSetSingleFieldFromModule($sequence, $objectType, $field, $forceObjectId = null)
    {
        /** Check if this test is Needed */
        if ($this->skipThisTest($sequence)) {
            return $this->assertTrue(true);
        }
        
        foreach ($this->objectVariantsProvider() as $variationData) {
            $this->currentVariation =   $variationData;
            parent::testSetSingleFieldFromModule($sequence, $objectType, $field, $forceObjectId);
        }
    }
    
    /**
     * @dataProvider objectFieldsProvider
     *
     * @param string $sequence
     * @param string $objectType
     * @param ArrayObject $field
     * @param null|string $forceObjectId
     */
    public function testSetSingleFieldFromService($sequence, $objectType, $field, $forceObjectId = null)
    {
        /** Check if this test is Needed */
        if ($this->skipThisTest($sequence)) {
            return $this->assertTrue(true);
        }
        
        foreach ($this->objectVariantsProvider() as $variationData) {
            $this->currentVariation =   $variationData;
            parent::testSetSingleFieldFromService($sequence, $objectType, $field, $forceObjectId);
        }
    }
    
    /**
     * Generate Fields Variations Attributes
     */
    public function objectVariantsProvider()
    {
        $result = array();
        
        $name   =  $this->getVariantName();
        for ($i=0; $i<3; $i++) {
            $result[]   =   array_merge($name, $this->getVariantAttributes(array('CustomA','CustomB')));
        }
        
        return $result;
    }

    /**
     * Generate Variations Multilang Name
     */
    public function getVariantName()
    {
        //====================================================================//
        //   Generate Random Attribute Name Values
        return array(
            "base_title"    =>  $this->encodeMultilang("Variant" . uniqid()),
        );
    }

    /**
     * Generate Variations Attributes
     *
     * @param array $attributesCodes
     */
    public function getVariantAttributes($attributesCodes)
    {
        $result = array();
        foreach ($attributesCodes as $code) {
            $result[] = $this->getVariantCustomAttribute($code);
        }

        return array("attributes" => $result);
    }
    
    /**
     * Generate Variations CustomAttribute
     *
     * @param string $attributesCode
     */
    public function getVariantCustomAttribute($attributesCode)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        // Multilang Mode is Simulated
        if (in_array($this->multilangMode(), array(self::$MULTILANG_DISABLED, self::$MULTILANG_SIMULATED), true)) {
            return array(
                "code"          =>  strtolower($attributesCode),
                "name_s"        =>  $attributesCode,
                "value_s"       =>  "Value" . rand(1000, 1010),
            );
        }
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return array(
                "code"          =>  strtolower($attributesCode),
                "name"          =>  $this->encodeMultilang($attributesCode),
                "value"         =>  $this->encodeMultilang("Value" . rand(1000, 1010)),
            );
        }
    }
    
    /**
     * Override Parent Function to Filter on Products Fields
     */
    public function objectFieldsProvider()
    {
        $fields = array();
        foreach (parent::objectFieldsProvider() as $field) {
            //====================================================================//
            // Filter Non Product Fields
            if ("Product" != $field[1]) {
                continue;
            }
            //====================================================================//
            // Filter Attribute Custom Fields
            if (false !== strpos($field[2]->id, "custom_attribute_pa_")) {
                continue;
            }
            //====================================================================//
            // DEBUG => Focus on a Specific Fields
            if ("image@images" == $field[2]->id) {
                continue;
            }
//            if ($Field[2]->id != "post_content") {
//                continue;
//            }
            $fields[] = $field;
        }

        return $fields;
    }
    
    /**
     * Override Parent Function to Add Variants Attributes
     *
     * @param string $objectType
     * @param ArrayObject $field
     * @param mixed $unik
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareForTesting($objectType, $field, $unik = true)
    {
        //====================================================================//
        //   Verify Test is Required
        if (!$this->verifyTestIsAllowed($objectType, $field)) {
            return false;
        }
        
        //====================================================================//
        // Prepare Fake Object Data
        //====================================================================//
        
        $this->fields   =   $this->fakeFieldsList($objectType, array($field->id), true);
        $fakeData       =   $this->fakeObjectData($this->fields);
 
        return array_merge($fakeData, $this->currentVariation);
    }

    /**
     * Shall we Skip this test?
     *
     * @param string $sequence
     *
     * @return boolean
     */
    private function skipThisTest($sequence)
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");

            return true;
        }
        /** Check if this Test sequence is Useful for this test */
        if (!in_array($sequence, array("Monolangual", "Multilangual"), true)) {
            return true;
        }
        $this->loadLocalTestSequence($sequence);

        return false;
    }
}
