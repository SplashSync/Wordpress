<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 *
 **/

namespace Splash\Tests;

use Splash\Client\Splash;

use Splash\Tests\WsObjects\O06SetTest;

use Splash\Local\Objects\Core\MultilangTrait;

/**
 * @abstract    Local Objects Test Suite - Specific Verifications for Products Variants.
 *
 * @author SplashSync <contact@splashsync.com>
 */
class L03VariantsCRUDTest extends O06SetTest
{
    use MultilangTrait;
    
    /**
     * @type    array
     */
    private $CurrentVariation   =   null;

    private function skipThisTest($Sequence)
    {
        /** Check if WooCommerce is active **/
        if (!Splash::local()->hasWooCommerce()) {
            $this->markTestSkipped("WooCommerce Plugin is Not Active");
            return true;
        }
        /** Check if this Test sequence is Useful for this test **/
        if (!in_array($Sequence, ["Monolangual", "Multilangual"])) {
            return true;
        } 
//        echo $Sequence;
        $this->loadLocalTestSequence($Sequence);
        return false;
    }
        
    /**
     * @dataProvider objectFieldsProvider
     */
    public function testSetSingleFieldFromModule($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        /** Check if this test is Needed **/
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
     */
    public function testSetSingleFieldFromService($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        /** Check if this test is Needed **/
        if ($this->skipThisTest($Sequence)) {
            return $this->assertTrue(true);
        }
        
        foreach ($this->objectVariantsProvider() as $VariationData) {
            $this->CurrentVariation =   $VariationData;
            parent::testSetSingleFieldFromService($Sequence, $ObjectType, $Field, $ForceObjectId);
        }
    }
    
    /**
     * @abstract    Generate Fields Variations Attributes
     */
    public function objectVariantsProvider()
    {
        $Result = array();
        
        $Name2   =  $this->getVariantName();
        for ($i=0; $i<3; $i++) {
            $Result[]   =   array_merge($Name2, $this->getVariantAttributes(['CustomA','CustomB']));
        }
        
        return $Result;
    }

    /**
     * @abstract    Generate Variations Multilang Name
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
     * @abstract    Generate Variations Attributes
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
     * @abstract    Generate Variations CustomAttribute
     */
    public function getVariantCustomAttribute($AttributesCode)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        // Multilang Mode is Simulated
        if ( in_array($this->multilangMode(), [self::$MULTILANG_DISABLED, self::$MULTILANG_SIMULATED]) ) {
            return array(
                "code"          =>  strtolower($AttributesCode),
                "name_s"        =>  $AttributesCode,
                "value_s"       =>  "Value" . rand(1E3,1E3 + 10),
            );
        }
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return array(
                "code"          =>  strtolower($AttributesCode),
                "name"          =>  $this->encodeMultilang($AttributesCode),
                "value"         =>  $this->encodeMultilang("Value" . rand(1E3,1E3 + 10)),
            );
        }        
    }
    
    /**
     * @abstract    Override Parent Function to Filter on Products Fields
     */
    public function objectFieldsProvider()
    {
        $Fields = array();
        foreach (parent::objectFieldsProvider() as $Field) {
            //====================================================================//
            // Filter Non Product Fields
            if ($Field[1] != "Product") {
                continue;
            }
            //====================================================================//
            // Filter Attribute Custom Fields
            if ( strpos($Field[2]->id , "custom_attribute_pa_") !== false) {              
                continue;
            }
            //====================================================================//
            // DEBUG => Focus on a Specific Fields
            if ($Field[2]->id == "image@images") {
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
     * @abstract    Override Parent Function to Add Variants Attributes
     */
    public function prepareForTesting($ObjectType, $Field)
    {
        //====================================================================//
        //   Verify Test is Required
        if (!$this->verifyTestIsAllowed($ObjectType, $Field)) {
            return false;
        }
        
        //====================================================================//
        // Prepare Fake Object Data
        //====================================================================//
        
        $this->Fields   =   $this->fakeFieldsList($ObjectType, [$Field->id], true);
        $FakeData       =   $this->fakeObjectData($this->Fields);

//var_dump(array_merge($FakeData, $this->CurrentVariation));
        
        return array_merge($FakeData, $this->CurrentVariation);
    }
}
