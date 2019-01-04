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
    protected $Out;
    
    /**
     * @type    array
     */
    private $CurrentVariation;
        
    /**
     * @dataProvider objectFieldsProvider
     *
     * @param string $Sequence
     * @param string $ObjectType
     * @param ArrayObject $Field
     * @param null|string $ForceObjectId
     */
    public function testSetSingleFieldFromModule($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        /** Check if this test is Needed */
        if ($this->skipThisTest($Sequence)) {
            return $this->assertTrue(true);
        }
        
        foreach ($this->objectVariantsProvider() as $VariationData) {
            $this->CurrentVariation =   $VariationData;
            parent::testSetSingleFieldFromModule($Sequence, $ObjectType, $Field, $ForceObjectId);
        }
    }
    
    /**
     * @dataProvider objectFieldsProvider
     *
     * @param string $Sequence
     * @param string $ObjectType
     * @param ArrayObject $Field
     * @param null|string $ForceObjectId
     */
    public function testSetSingleFieldFromService($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        /** Check if this test is Needed */
        if ($this->skipThisTest($Sequence)) {
            return $this->assertTrue(true);
        }
        
        foreach ($this->objectVariantsProvider() as $VariationData) {
            $this->CurrentVariation =   $VariationData;
            parent::testSetSingleFieldFromService($Sequence, $ObjectType, $Field, $ForceObjectId);
        }
    }
    
    /**
     * Generate Fields Variations Attributes
     */
    public function objectVariantsProvider()
    {
        $Result = array();
        
        $Name2   =  $this->getVariantName();
        for ($i=0; $i<3; $i++) {
            $Result[]   =   array_merge($Name2, $this->getVariantAttributes(array('CustomA','CustomB')));
        }
        
        return $Result;
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
     * @param array $AttributesCodes
     */
    public function getVariantAttributes($AttributesCodes)
    {
        $Result = array();
        foreach ($AttributesCodes as $Code) {
            $Result[] = $this->getVariantCustomAttribute($Code);
        }

        return array("attributes" => $Result);
    }
    
    /**
     * Generate Variations CustomAttribute
     *
     * @param string $AttributesCode
     */
    public function getVariantCustomAttribute($AttributesCode)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        // Multilang Mode is Simulated
        if (in_array($this->multilangMode(), array(self::$MULTILANG_DISABLED, self::$MULTILANG_SIMULATED), true)) {
            return array(
                "code"          =>  strtolower($AttributesCode),
                "name_s"        =>  $AttributesCode,
                "value_s"       =>  "Value" . rand(1000, 1010),
            );
        }
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return array(
                "code"          =>  strtolower($AttributesCode),
                "name"          =>  $this->encodeMultilang($AttributesCode),
                "value"         =>  $this->encodeMultilang("Value" . rand(1000, 1010)),
            );
        }
    }
    
    /**
     * Override Parent Function to Filter on Products Fields
     */
    public function objectFieldsProvider()
    {
        $Fields = array();
        foreach (parent::objectFieldsProvider() as $Field) {
            //====================================================================//
            // Filter Non Product Fields
            if ("Product" != $Field[1]) {
                continue;
            }
            //====================================================================//
            // Filter Attribute Custom Fields
            if (false !== strpos($Field[2]->id, "custom_attribute_pa_")) {
                continue;
            }
            //====================================================================//
            // DEBUG => Focus on a Specific Fields
            if ("image@images" == $Field[2]->id) {
                continue;
            }
//            if ($Field[2]->id != "post_content") {
//                continue;
//            }
            $Fields[] = $Field;
        }

        return $Fields;
    }
    
    /**
     * Override Parent Function to Add Variants Attributes
     *
     * @param string $ObjectType
     * @param ArrayObject $Field
     * 
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function prepareForTesting($ObjectType, $Field, $unik = true)
    {
        //====================================================================//
        //   Verify Test is Required
        if (!$this->verifyTestIsAllowed($ObjectType, $Field)) {
            return false;
        }
        
        //====================================================================//
        // Prepare Fake Object Data
        //====================================================================//
        
        $this->fields   =   $this->fakeFieldsList($ObjectType, array($Field->id), true);
        $FakeData       =   $this->fakeObjectData($this->fields);
 
        return array_merge($FakeData, $this->CurrentVariation);
    }

    private function skipThisTest($Sequence)
    {
        /** Check if WooCommerce is active */
        if (!Local::hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");

            return true;
        }
        /** Check if this Test sequence is Useful for this test */
        if (!in_array($Sequence, array("Monolangual", "Multilangual"), true)) {
            return true;
        }
        $this->loadLocalTestSequence($Sequence);

        return false;
    }
}
