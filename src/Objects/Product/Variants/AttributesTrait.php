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
 * @abstract    WooCommerce Product Variants Attributes Data Access
 */
trait AttributesTrait
{

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Attributes Fields using FieldFactory
    */
    private function buildVariantsAttributesFields()
    {
        $Group  =  __("Variations");

        //====================================================================//
        // Detect Multilangual Mode
        if ($this->multilangMode() != self::$MULTILANG_DISABLED) {
            $VarcharType    = SPL_T_MVARCHAR;
        } else {
            $VarcharType    = SPL_T_VARCHAR;
        }
        
        //====================================================================//
        // Product Variation List - Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("code")
                ->Name(__("Code"))
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeCode")
                ->isNotTested();

        //====================================================================//
        // Product Variation List - Variation Attribute Name
        $this->fieldsFactory()->create($VarcharType)
                ->Identifier("name")
                ->Name(__("Name"))
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeName")
                ->isNotTested();

        //====================================================================//
        // Product Variation List - Variation Attribute Value
        $this->fieldsFactory()->create($VarcharType)
                ->Identifier("value")
                ->Name(__("Value"))
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeValue")
                ->isNotTested();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * @abstract     Read requested Field
     *
     * @param        string    $Key                    Input List Key
     * @param        string    $FieldName              Field Identifier / Name
     *
     * @return       void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getVariantsAttributesFields($Key, $FieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $FieldId = self::lists()->initOutput($this->Out, "attributes", $FieldName);
        if (!$FieldId) {
            return;
        }
        if (!$this->isVariantsProduct()) {
            unset($this->In[$Key]);
            return;
        }

        //====================================================================//
        // READ Fields
        foreach ($this->Product->get_attributes() as $Code => $Name) {
            //====================================================================//
            // Load Attribute Group
            $GroupId        =   $this->getAttributeGroupByCode($Code);
            $Group          =   wc_get_attribute($GroupId);
            //====================================================================//
            // Load Attribute
            $AttributeId    =   $this->getAttributeByCode($Code, $Name);
            $Attribute      =   get_term($AttributeId);
            $AttributeName  =   isset($Attribute->name) ? $Attribute->name : null;

            switch ($FieldId) {
                case 'code':
                    $Value  =   str_replace('pa_', '', $Code);
                    break;

                case 'name':
                    $Value  =   $this->encodeMultilang($Group->name);
                    break;

                case 'value':
                    $Value  =   $this->encodeMultilang($AttributeName);
                    break;

                default:
                    return;
            }
            self::lists()->insert($this->Out, "attributes", $FieldId, $Code, $Value);
        }
        unset($this->In[$Key]);
        Splash::log()->www("Read Attributes", $this->Out["attributes"]);            

    }

    //====================================================================//
    // CRUD Functions
    //====================================================================//

    /**
     * @abstract    Check if Attribute Array is Valid for Writing
     * @param       mixed       $Data       Attribute Array
     * @return      bool
     */
    private function isValidAttributeDefinition($Data)
    {
        //====================================================================//
        // Check Attribute is Array
        if ((!is_array($Data) && !is_a($Data, "ArrayObject") ) || empty($Data)) {
            return false;
        }
        //====================================================================//
        // Check Attributes Code is Given
        if (!isset($Data["code"]) || !is_string($Data["code"]) || empty($Data["code"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Code is Not Valid."
            );
        }
        //====================================================================//
        // Check Attributes Values With Multilang detection
        if ($this->multilangMode() != self::$MULTILANG_DISABLED) {
            return $this->isValidMultilangAttributeDefinition($Data);
        }
        return $this->isValidMonolangAttributeDefinition($Data);
    }
    
    /**
     * @abstract    Check if Attribute Array is Valid Monolangual Attribute Definition
     * @param       array       $Data       Attribute Array
     * @return      bool
     */
    private function isValidMonolangAttributeDefinition($Data)
    {
        //====================================================================//
        // Check Attributes Names are Given
        if (!isset($Data["name"]) || !is_scalar($Data["name"]) || empty($Data["name"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Public Name is Not Valid."
            );
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (!isset($Data["value"]) || !is_scalar($Data["value"]) || empty($Data["value"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Value Name is Not Valid."
            );
        }
        return true;
    }
    
    /**
     * @abstract    Check if Attribute Array is Valid Multilangual Attribute Definition
     * @param       array       $Data       Attribute Array
     * @return      bool
     */
    private function isValidMultilangAttributeDefinition($Data)
    {
        //====================================================================//
        // Check Attributes Names are Given
        if (!isset($Data["name"]) || empty($Data["name"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Public Name is Not Valid."
            );
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (!isset($Data["value"]) || empty($Data["value"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Value Name is Not Valid."
            );
        }
        return true;
    }
    
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * @abstract    Write Given Fields
     *
     * @param       string  $FieldName      Field Identifier / Name
     * @param       mixed   $Data           Field Data
     *
     * @return      void
     */
    private function setVariantsAttributesFields($FieldName, $Data)
    {
        //====================================================================//
        // Safety Check
        if ($FieldName !== "attributes") {
            return;
        }

        //====================================================================//
        // Update Products Attributes Ids
        $NewAttributes  =   array();
        foreach ($Data as $Item) {
            //====================================================================//
            // Check Product Attributes are Valid
            if (!$this->isValidAttributeDefinition($Item)) {
                continue;
            }
            //====================================================================//
            // Extract Attribute Informations
            $Code   =   $Item["code"];
            $Name   =   $Item["name"];
            $Value  =   $Item["value"];
            //====================================================================//
            // Identify or Add Attribute Group Id
            $AttributeGroupId   =   $this->getVariantsAttributeGroup($Code, $Name);
            if (!$AttributeGroupId) {
                continue;
            }
            //====================================================================//
            // Identify or Add Attribute Id
            $AttributeId   =   $this->getVariantsAttributeValue($Code, $Value);
            if (!$AttributeId) {
                continue;
            }
            //====================================================================//
            // Load Attribute Class
            $Attribute  =   get_term($AttributeId);
            $NewAttributes[wc_attribute_taxonomy_name($Code)] = $Attribute->slug;
        }
        
        //====================================================================//
        // Update Combination if Modified
        if ($this->Product->get_attributes() != $NewAttributes) {
Splash::log()->www("received Attributes", $Data);            
Splash::log()->www("New Attributes", $NewAttributes);            
            foreach ($NewAttributes as $Key => $Value) {
                $this->setPostMeta("attribute_" . $Key, $Value);
            }
        }

        unset($this->In[$FieldName]);
    }

    /**
     * @abstract    Ensure Product Attribute Group Exists
     * @param       string      $Code       Attribute Group Code
     * @param       string      $Name       Attribute Group Name
     * @return      int|false
     */
    private function getVariantsAttributeGroup($Code, $Name)
    {
        //====================================================================//
        // Load Product Attribute Group
        $AttributeGroupId   =   $this->getAttributeGroupByCode($Code);
        if (!$AttributeGroupId) {
            //====================================================================//
            // Add Product Attribute Group
            $AttributeGroupId = $this->addAttributeGroup($Code, $Name);
        }
        //====================================================================//
        // DEBUG MODE => Update Group Names
        if (defined("SPLASH_DEBUG") && !empty(SPLASH_DEBUG)) {
            wc_update_attribute($AttributeGroupId, array(
                "slug"  =>   $Code,
                "name"  =>   $this->decodeMultilang($Name)
            ));
        }
        //====================================================================//
        // An Error Occured
        if (!$AttributeGroupId) {
            return false;
        }
        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        $this->assignAttributeGroup($this->BaseProduct, $AttributeGroupId, $Code);

        return $AttributeGroupId;
    }

    /**
     * @abstract    Ensure Product Attribute Group Exists
     * @param       string      $Slug       Attribute Group Slug
     * @param       string      $Value      Attribute Value
     * @return      int|false
     */
    private function getVariantsAttributeValue($Slug, $Value)
    {
        //====================================================================//
        // Load Product Attribute Value
        $AttributeId   =   $this->getAttributeByName($Slug, $Value);
        if (!$AttributeId) {
            //====================================================================//
            // Add Product Attribute Value
            $AttributeId = $this->addAttributeValue($Slug, $Value);
        }
        if (!$AttributeId) {
            return false;
        }

        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        $this->assignAttribute($this->BaseProduct, $Slug, $AttributeId);

        return $AttributeId;
    }

    /**
     * @abstract    Build Product Attribute Definition Array
     * @param       WC_Product      $Product          Product Object
     * @return      array
     */
    public function getProductAttributesArray($Product)
    {
        $Result =   array();

        if (empty($Product->get_parent_id())) {
            return $Result;
        }

        foreach ($Product->get_attributes() as $Key => $Attribute) {
            $Key    = str_replace('pa_', '', $Key);
            //====================================================================//
            // Add Attribute Value to Definition Array
            $Result[$Key]   =   $Attribute;
        }
        return $Result;
    }
}
