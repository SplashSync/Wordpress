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

use WC_Product_Attribute;

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
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("name_s")
                ->Name(__("Name"))
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeNameSimple");
        if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
            $this->fieldsFactory()->isReadOnly();
        } else {
            $this->fieldsFactory()->isNotTested();
        }

        //====================================================================//
        // Product Variation List - Variation Attribute Name (MultiLang)
        $this->fieldsFactory()->create(SPL_T_MVARCHAR)
                ->Identifier("name")
                ->Name(__("Name") . " (M)")
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeName");
        if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
            $this->fieldsFactory()->isNotTested();
        } else {
            $this->fieldsFactory()->isReadOnly();
        }
        
        //====================================================================//
        // Product Variation List - Variation Attribute Value
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("value_s")
                ->Name(__("Value"))
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeValueSimple");
        if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
            $this->fieldsFactory()->isReadOnly();
        } else {
            $this->fieldsFactory()->isNotTested();
        }
        
        //====================================================================//
        // Product Variation List - Variation Attribute Value (MultiLang)
        $this->fieldsFactory()->create(SPL_T_MVARCHAR)
                ->Identifier("value")
                ->Name(__("Value") . " (M)")
                ->InList("attributes")
                ->Group($Group)
                ->MicroData("http://schema.org/Product", "VariantAttributeValue");
        if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
            $this->fieldsFactory()->isNotTested();
        } else {
            $this->fieldsFactory()->isReadOnly();
        }
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
        
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
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
                
                case 'name_s':
                    $Value  =   $this->extractMultilangValue($Group->name);
                    break;
                
                case 'name':
                    $Value  =   $this->encodeMultilang($Group->name);
                    break;
                
                case 'value_s':
                    $Value  =   $this->extractMultilangValue($AttributeName);
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
    }

    //====================================================================//
    // CRUD Functions
    //====================================================================//

//    /**
//     * @abstract    Check if New Product is a Variant Product
//     * @param       array       $Data       Input Field Data
//     * @return      bool
//     */
//    private function isNewVariant($Data)
//    {
//        //====================================================================//
//        // Check Product Attributes are given
//        if (!isset($Data["attributes"]) || empty($Data["attributes"])) {
//            return false;
//        }
//        //====================================================================//
//        // Check Product Attributes are Valid
//        foreach ($Data["attributes"] as $AttributeArray) {
//            if (!$this->isValidAttributeDefinition($AttributeArray)) {
//                return false;
//            }
//        }
//        return true;
//    }
//
    /**
     * @abstract    Check if Attribute Array is Valid for Writing
     * @param       array       $Data       Attribute Array
     * @return      bool
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
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
        if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
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
        } else {
            //====================================================================//
            // Check Attributes Names are Given
            if (!isset($Data["name_s"]) || !is_scalar($Data["name_s"]) || empty($Data["name_s"])) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Product Attribute Public Name is Not Valid."
                );
            }
            //====================================================================//
            // Check Attributes Values are Given
            if (!isset($Data["value_s"]) || !is_scalar($Data["value_s"]) || empty($Data["value_s"])) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Product Attribute Value Name is Not Valid."
                );
            }
        }
        return true;
    }
//
//    /**
//     * @abstract    Search for Base Product by Multilang Name
//     * @param       array       $Name       Input Product Name without Options Array
//     * @return      int|null    Product Id
//     */
//    public function getBaseProduct($Name)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__, __FUNCTION__);
//        //====================================================================//
//        // Check Name is Array
//        if ((!is_array($Name) && !is_a($Name, "ArrayObject") ) || empty($Name)) {
//            return null;
//        }
//        //====================================================================//
//        // For Each Available Language
//        foreach (Language::getLanguages() as $Lang) {
//            //====================================================================//
//            // Encode Language Code From Splash Format to Prestashop Format (fr_FR => fr-fr)
//            $LanguageCode   =   Splash::local()->langEncode($Lang["language_code"]);
//            $LanguageId     =   (int) $Lang["id_lang"];
//            //====================================================================//
//            // Check if Name is Given in this Language
//            if (!isset($Name[$LanguageCode])) {
//                continue;
//            }
//            //====================================================================//
//            // Search for this Base Product Name
//            $BaseProductId   = $this->searchBaseProduct($LanguageId, $Name[$LanguageCode]);
//            if ($BaseProductId) {
//                return $BaseProductId;
//            }
//        }
//        return null;
//    }
//
//    /**
//     * @abstract    Search for Base Product by Multilang Name
//     * @param       int         $LangId     Prestashop Language Id
//     * @param       array       $Name       Input Product Name without Options Array
//     * @return      int|null    Product Id
//     */
//    private function searchBaseProduct($LangId, $Name)
//    {
//        //====================================================================//
//        // Stack Trace
//        Splash::log()->trace(__CLASS__, __FUNCTION__);
//        //====================================================================//
//        // Check Name is Array
//        if (empty($Name)) {
//            return null;
//        }
//        //====================================================================//
//        // Build query
//        $sql = new DbQuery();
//        $sql->select("p.`id_product`            as id");
//        $sql->select("pl.`name` as name");
//        $sql->from("product", 'p');
//        $sqlWhere = '(pl.id_product = p.id_product AND pl.id_lang = ';
//        $sqlWhere.= (int)  $LangId.Shop::addSqlRestrictionOnLang('pl').')';
//        $sql->leftJoin("product_lang", 'pl', $sqlWhere);
//        $sql->where(" LOWER( pl.name )         LIKE LOWER( '%" . pSQL($Name) ."%') ");
//        //====================================================================//
//        // Execute final request
//        $Result = Db::getInstance()->executeS($sql);
//        if (Db::getInstance()->getNumberError()) {
//            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, Db::getInstance()->getMsgError());
//        }
//        //====================================================================//
//        // Analyse Resuslts
//        if (isset($Result[0]["id"])) {
//            return $Result[0]["id"];
//        }
//        return null;
//    }
//
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
            return true;
        }
        
//        if (!empty($Data)) {
//            unset($this->In[$Key]);
//            return;
//        }
        
//        if (!$this->Attribute) {
//            return true;
//        }
        
//Splash::log()->www("Data", $Data);
//Splash::log()->www("Attributes", $this->Product->get_attributes());
        
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
            if (in_array(self::multilangMode(), [self::$MULTILANG_WPMU])) {
                $Name   =   $this->decodeMultilang($Item["name"]);
                $Value  =   $this->decodeMultilang($Item["value"]);
            } else {
                $Name   =   $Item["name_s"];
                $Value  =   $Item["value_s"];
            }
            
            //====================================================================//
            // Identify or Add Attribute Group Id
            $AttributeGroupId   =   $this->getVariantsAttributeGroup($Code, $Name);
            if (!$AttributeGroupId) {
                continue;
            }
//Splash::log()->www("AttributeGroupId", $AttributeGroupId);
            //====================================================================//
            // Identify or Add Attribute Id
            $AttributeId   =   $this->getVariantsAttributeValue($Code, $Value);
            if (!$AttributeId) {
                continue;
            }
            //====================================================================//
            // Load Attribute Class
            $Attribute  =   get_term($AttributeId);
//Splash::log()->www("AttributeId", $AttributeId);
            $NewAttributes[wc_attribute_taxonomy_name($Code)] = $Attribute->slug;
        }
//
//        //====================================================================//
//        // Build Current Attributes Ids Table
//        $OldAttributesIds = array();
//        foreach ($this->Attribute->getWsProductOptionValues() as $Attribute) {
//            $OldAttributesIds[] = $Attribute["id"];
//        }
//
//Splash::log()->www("Attribute", $this->BaseProduct->get_attributes());
        //====================================================================//
        // Update Combination if Modified
        if ($this->Product->get_attributes() != $NewAttributes) {
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
     * @return      string      $Slug       Attribute Group Slug
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
     * @return      int|false
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
