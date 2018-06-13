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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;

use WC_Product;

/**
 * @abstract    Prestashop Product Variant Core Data Access
 */
trait CoreTrait
{
    
    /**
     * @var WC_Post
     */
    protected $BaseObject  = null;
    
    /**
     * @var WC_Product
     */
    protected $BaseProduct  = null;
    
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildVariantsCoreFields()
    {
       
        //====================================================================//
        // Product Type Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("type")
                ->Name('Product Type')
                ->Group("Meta")
                ->addChoices(["simple" => "Simple", "variant" => "Variant"])
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
        $this->fieldsFactory()->create(self::objects()->encode("Product", SPL_T_ID))
                ->Identifier("default_id")
                ->Name('Default Variant')
                ->Group("Meta")
                ->MicroData("http://schema.org/Product", "DefaultVariation")
                ->isNotTested();
        
        //====================================================================//
        // Product Variation Parent Link
        $this->fieldsFactory()->create(self::objects()->encode("Product", SPL_T_ID))
                ->Identifier("parent_id")
                ->Name("Parent")
                ->Group("Meta")
                ->MicroData("http://schema.org/Product", "isVariationOf")
                ->isReadOnly();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     *  @abstract     Check if Current product is Variant of Base Product
     *  @return       bool
     */
    protected function isVariantsProduct()
    {
        return !empty($this->Product->get_parent_id());
    }
    
    /**
     *  @abstract     Check if Given Product ID is Base Product of Variants
     *  @return       false|array       False or Array of Childrens Ids
     */
    protected static function isBaseProduct($PostId)
    {
        $Childrens  =  get_children([
            'post_type'     => "product_variation",
            'post_parent'   => $PostId,
        ]);
        if (sizeof($Childrens) > 0) {
            return array_keys($Childrens);
        }
        return false;
    }
    
    /**
     *  @abstract     Decide which IDs needs to be commited
     *  @return       array
     */
    public static function getIdsForCommit($PostId)
    {
        $Childrens =    self::isBaseProduct($PostId);
        if ($Childrens) {
            return $Childrens;
        }
        return $PostId;
    }
    
    /**
     * @abstract    Load WooCommerce Parent Product
     * @return      void
     */
    public function loadParent()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Check if Product is Variant Product
        if (!$this->isVariantsProduct()) {
            return;
        }
        //====================================================================//
        // Prevent Commit for Parent Product
        $this->lock($this->Product->get_parent_id());
        //====================================================================//
        // Load WooCommerce Parent Product Object
        $Product  =       wc_get_product($this->Product->get_parent_id());
        if ($Product) {
            $this->BaseProduct  =       $Product;
            $this->BaseObject   =       get_post($this->Product->get_parent_id());
        }
        if (is_wp_error($Product)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load Parent Product (" . $this->Product->get_parent_id() . ")."
            );
        }
    }
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    private function getVariantsCoreFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'parent_id':
                if ($this->isVariantsProduct()) {
                    $this->Out[$FieldName] = self::objects()->encode("Product", $this->Product->get_parent_id());
                    break;
                }
                $this->Out[$FieldName] = null;
                break;
                
            case 'type':
                if ($this->isVariantsProduct()) {
                    $this->Out[$FieldName]  =   "variant";
                } else {
                    $this->Out[$FieldName]  =   "simple";
                }
                break;
                
            case 'default_on':
                if ($this->isVariantsProduct()) {
                    $this->Out[$FieldName]  =   ($this->Product->get_menu_order() == 1);
                } else {
                    $this->Out[$FieldName]  =   false;
                }
                break;
            
            case 'default_id':
                if ($this->isVariantsProduct()) {
//                    $UnikId     =   (int) $this->getUnikId(
//                        $this->ProductId,
//                        $this->Object->getDefaultIdProductAttribute()
//                    );
//                    $this->Out[$FieldName] = self::objects()->encode("Product", $UnikId);
                    $this->Out[$FieldName]  =   null;
                } else {
                    $this->Out[$FieldName]  =   null;
                }
                break;
            
            default:
                return;
        }
        
//        if (!isset($this->In[$Key])) {
//Splash::log()->www("field", $FieldName);
//        }

//        if (isset($this->In[$Key])) {
            unset($this->In[$Key]);
//        }
    }
    
    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return         none
     */
    private function setVariantsCoreFields($FieldName, $Data)
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case 'default_on':
                break;
            
            case 'default_id':
                $Data = $Data;
//                //====================================================================//
//                // Check if Valid Data
//                if (!$this->AttributeId || ($this->ProductId != $this->getId($Data))) {
//                    break;
//                }
//                $AttributeId    =     $this->getAttribute($Data);
//                if (!$AttributeId || ($AttributeId == $this->Object->getDefaultIdProductAttribute())) {
//                    break;
//                }
//                $this->Object->deleteDefaultAttributes();
//                $this->Object->setDefaultAttribute($AttributeId);
                break;
            
            default:
                return;
        }
        unset($this->In[$FieldName]);
    }
}
