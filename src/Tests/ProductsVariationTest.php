<?php
namespace Splash\Local\Tests\Products;

use Splash\Client\Splash;
use Splash\Models\Objects\ObjectsHelper;
use Splash\Tests\WsObjects\O07SetTest;

/**
 * @abstract    Wordpress Local Test Suite - Generate & Tests Dummy Variable Product & Variations
 *
 * @author SplashSync <contact@splashsync.com>
 */
class ProductsVariationTest extends O07SetTest
{
    
    const MAX_VARIATIONS            =   3;
    const VARIABLE_PRODUCT          =   "PhpUnit-Product-Variable";
    const VARIATION_PRODUCT         =   "PhpUnit-Product-Varition";
    
    protected function setUp()
    {
        //====================================================================//
        // BOOT or REBOOT MODULE
        Splash::Reboot();
        
        //====================================================================//
        // FAKE SPLASH SERVER HOST URL
        Splash::Configuration()->WsHost = "No.Commit.allowed.not";
        
        //====================================================================//
        // Load Module Local Configuration (In Safe Mode)
        //====================================================================//
        $this->loadLocalTestParameters();
        
        //====================================================================//
        // Create Variable Product & Variations
        //====================================================================//
        
        //====================================================================//
        // Check or Create Product Test Attribute
        $this->VariableProduct  =   $this->createVariableProduct();
        
        //====================================================================//
        // Check or Create Product Test Attribute
        $this->Variations       =   $this->createVariations();
    }
    
    //====================================================================//
    //   Functionnal Tests
    //====================================================================//

    public function testProductBase()
    {
        $this->assertNotEmpty($this->VariableProduct);
        $this->assertInstanceOf("WC_Product_Variable", $this->VariableProduct);
        
        $this->assertEquals(self::MAX_VARIATIONS, count($this->VariableProduct->get_children()));
    }

    public function testProductVariations()
    {
        $this->assertNotEmpty($this->Variations);
        $this->assertEquals(self::MAX_VARIATIONS, count($this->Variations));
        foreach ($this->Variations as $Variation) {
            $this->assertInstanceOf("WC_Product_Variation", $Variation);
        }
    }

    public function testVariableProductLinksFromService()
    {
        //====================================================================//
        //   Create Fields List
        $Fields     =   array(
            "parent_id",
            "id@children",
            "sku@children",
            "attribute@children",
        );
        //====================================================================//
        //   Read Object Data
        $Data    =   Splash::Object("Product")
                ->Get($this->VariableProduct->get_id(), $Fields);

        //====================================================================//
        //   Verify Data
        $this->assertNotEmpty($Data);
        $this->assertEmpty($Data["parent_id"]);
        $this->assertNotEmpty($Data["children"]);
        foreach ($this->Variations as $Variation) {
            $VarData    =   array_shift($Data["children"]);
            $this->assertNotEmpty($VarData);
            $this->assertEquals(ObjectsHelper::Encode("Product", $Variation->get_id()), $VarData["id"]);
            $this->assertEquals($Variation->get_sku(), $VarData["sku"]);
            $this->assertEquals(implode(" | ", $Variation->get_attributes()), $VarData["attribute"]);
        }
    }
    
    public function testVariationProductLinksFromService()
    {
        //====================================================================//
        //   Create Fields List
        $Fields     =   array(
            "parent_id",
            "id@children",
            "sku@children",
            "attribute@children",
        );
        
        foreach ($this->Variations as $Variation) {
            //====================================================================//
            //   Read Object Data
            $Data    =   Splash::Object("Product")->Get($Variation->get_id(), $Fields);

            //====================================================================//
            //   Verify Data
            $this->assertNotEmpty($Data);
            
            $this->assertNotEmpty($Data["parent_id"]);
            $this->assertEquals(ObjectsHelper::Encode("Product", $this->VariableProduct->get_id()), $Data["parent_id"]);
            
            $this->assertEquals(0, count($Data["children"]));
        }
    }
    
    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromModule($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->Variations as $ProductVariation) {
            parent::testSingleFieldFromModule($Sequence, $ObjectType, $Field, $ProductVariation->get_id());
        }
    }
    
    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromService($Sequence, $ObjectType, $Field, $ForceObjectId = null)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->Variations as $ProductVariation) {
            parent::testSingleFieldFromModule($Sequence, $ObjectType, $Field, $ProductVariation->get_id());
        }
    }
    
    
    //====================================================================//
    //   Manage Variables Products
    //====================================================================//
    
    public function loadVariableProduct()
    {
        //====================================================================//
        // Load From DataBase
        $Post   =   array_shift(get_posts([
            'post_type'         =>      "product",
            'post_status'       =>      array_keys(get_post_statuses()),
            'title'             =>      self::VARIABLE_PRODUCT,
        ]));
        
        if (empty($Post)) {
            return null;
        }
        
        return wc_get_product($Post->ID);
    }
    
    public function createVariableProduct()
    {
        $Product = $this->loadVariableProduct();
        
        if (!empty($Product)) {
            return $Product;
        }
        
        $Id  =   wp_insert_post(array(
            "post_type"     =>  "product",
            "post_title"    =>  self::VARIABLE_PRODUCT,
        ));
           
        $Variations = array();
        for ($i=0; $i<self::MAX_VARIATIONS; $i++) {
            $Variations[]   =   "Type-" . $i;
        }
        wp_set_object_terms($Id, 'variable', 'product_type');
        update_post_meta($Id, "_product_attributes", array(
            "variant"    => array(
                "name"          =>  "Variant",
                "value"         =>  implode(" | ", $Variations),
                "position"      =>  0,
                "is_visible"    =>  1,
                "is_variation"  =>  1,
                "is_taxonomy"   =>  0,

            )
        ));

        return wc_get_product($Id);
    }
    
    public function createVariations()
    {
        $Variations     = array();
        $NewChildrens   = array();
        if (empty($this->VariableProduct)) {
            return $Variations;
        }
        
        $Childrens   =   $this->VariableProduct->get_children();
        
        for ($i=0; $i<self::MAX_VARIATIONS; $i++) {
            //====================================================================//
            // Load Existing Product Variation
            $Id         =   array_shift($Childrens);
            if (!empty($Id)) {
                $Variations[]   =   wc_get_product($Id);
                $NewChildrens[] =   $Id;
                continue;
            }
            
            //====================================================================//
            // Create Product Variation
            $VariationId  =   wp_insert_post(array(
                "post_type"     =>  "product_variation",
                "post_title"    =>  self::VARIATION_PRODUCT . "-" . $i,
                "post_name"     =>  self::VARIATION_PRODUCT . "-" . $i,
                "post_parent"   =>  $this->VariableProduct->get_id(),
                "post_status"   =>  "publish",
                "menu_order"    =>  ($i + 1),
            ));
            
            update_post_meta($VariationId, "attribute_variant", "Type-" . $i);

            $Variations[]   =   wc_get_product($VariationId);
            $NewChildrens[] =   $VariationId;
        }
        
        if (serialize($Childrens) != serialize($NewChildrens)) {
            $this->VariableProduct->set_children($NewChildrens);
            $this->VariableProduct->save();
        }
        
        return $Variations;
    }
    
    //====================================================================//
    //   Data Provider Functions
    //====================================================================//
    
    public function ObjectFieldsProvider()
    {
        //====================================================================//
        //   Object & Feilds Scope
        $ObjectType =   "Product";
        $Fields     =   array("_weight", "_height", "_length", "_width", "_stock", "_regular_price", "_thumbnail_id");
        
        $Result     = array();
        
        //====================================================================//
        //   Filter Object Fields
        $ObjectFields   =   Splash::Object($ObjectType)->Fields();
        $FilteredFields =   $this->filterFieldList($ObjectFields, $Fields);
        foreach ($FilteredFields as $Field) {
            $Result[] = array($ObjectType, $Field);
        }
        return $Result;
    }
}
