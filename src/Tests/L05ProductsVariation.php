<?php
namespace Splash\Local\Tests;

use Splash\Client\Splash;
use Splash\Models\Helpers\ObjectsHelper;
use Splash\Tests\WsObjects\O06SetTest;

use WC_Product;
use WC_Product_Variable;

/**
 * @abstract    Wordpress Local Test Suite - Generate & Tests Dummy Variable Product & Variations
 *
 * @author SplashSync <contact@splashsync.com>
 */
class L05ProductsVariation extends O06SetTest
{

    const MAX_VARIATIONS            =   3;
    const VARIABLE_PRODUCT          =   "PhpUnit-Product-Variable";
    const VARIATION_PRODUCT         =   "PhpUnit-Product-Varition";

    /**
     * @var WC_Product_Variable
     */
    private $VariableProduct =   null;

    /**
     * @var array
     */
    private $Variations =   null;

    protected function setUp()
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
        $Product    =   $this->createVariableProduct();
        if ($Product) {
            $this->VariableProduct  =   $Product;
        }

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
        $Data    =   Splash::object("Product")
                ->get((string)$this->VariableProduct->get_id(), $Fields);

        //====================================================================//
        //   Verify Data
        $this->assertNotEmpty($Data);
        $this->assertEmpty($Data["parent_id"]);
        $this->assertNotEmpty($Data["children"]);
        foreach ($this->Variations as $Variation) {
            $VarData    =   array_shift($Data["children"]);
            $this->assertNotEmpty($VarData);
            $this->assertEquals(ObjectsHelper::encode("Product", (string)$Variation->get_id()), $VarData["id"]);
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
            $Data    =   Splash::object("Product")->get((string)$Variation->get_id(), $Fields);

            //====================================================================//
            //   Verify Data
            $this->assertNotEmpty($Data);

            $this->assertNotEmpty($Data["parent_id"]);
            $this->assertEquals(
                ObjectsHelper::encode("Product", (string)$this->VariableProduct->get_id()),
                $Data["parent_id"]
            );

            $this->assertEquals(0, count($Data["children"]));
        }
    }

    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromModule($Sequence, $ObjectType, $Field)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->Variations as $ProductVariation) {
            parent::testSetSingleFieldFromModule($Sequence, $ObjectType, $Field, $ProductVariation->get_id());
        }
    }

    /**
     * @dataProvider ObjectFieldsProvider
     */
    public function testSingleFieldFromService($Sequence, $ObjectType, $Field)
    {
        //====================================================================//
        //   For Each Product Variation
        foreach ($this->Variations as $ProductVariation) {
            parent::testSetSingleFieldFromService($Sequence, $ObjectType, $Field, $ProductVariation->get_id());
        }
    }


    //====================================================================//
    //   Manage Variables Products
    //====================================================================//

    /**
     * @abstract    Load a Variable Product
     * @return      false|WC_Product_Variable|null
     */
    public function loadVariableProduct()
    {
        //====================================================================//
        // Load From DataBase
        $Posts  =   get_posts([
            'post_type'         =>      "product",
            'post_status'       =>      array_keys(get_post_statuses()),
            'title'             =>      self::VARIABLE_PRODUCT,
        ]);
        if (empty($Posts)) {
            return null;
        }

        $Post   =   array_shift($Posts);

        /** @var WC_Product_Variable $VariableProduct */
        $VariableProduct    =   wc_get_product($Post->ID);
        return $VariableProduct;
    }

    /**
     * @abstract    Create a Variable Product
     * @return false|WC_Product_Variable|null
     */
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

        if (is_integer($Id)) {
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
        }

        /** @var WC_Product_Variable $VariableProduct */
        $VariableProduct    =   wc_get_product($Id);
        return $VariableProduct;
    }

    /**
     * @abstract    Create a Product Varaitions
     * @return      array
     */
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

            if (is_int($VariationId)) {
                update_post_meta($VariationId, "attribute_variant", "Type-" . $i);
            }

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

    public function objectFieldsProvider()
    {
        //====================================================================//
        //   Object & Feilds Scope
        $ObjectType =   "Product";
        $Fields     =   array("_weight", "_height", "_length", "_width", "_stock", "_regular_price", "_thumbnail_id");

        $Result     = array();

        //====================================================================//
        // Check if Local Tests Sequences are defined
        if (method_exists(Splash::local(), "TestSequences")) {
            $Sequences  =   Splash::local()->testSequences("List");
        } else {
            $Sequences  =   array( 1 => "None");
        }

        //====================================================================//
        //   For Each Test Sequence
        foreach ($Sequences as $Sequence) {
            $this->loadLocalTestSequence($Sequence);
            //====================================================================//
            //   Filter Object Fields
            $ObjectFields   =   Splash::object($ObjectType)->fields();
            $FilteredFields =   $this->filterFieldList($ObjectFields, $Fields);
            foreach ($FilteredFields as $Field) {
                $Result[] = array($Sequence, $ObjectType, $Field);
            }
        }

        return $Result;
    }
}
