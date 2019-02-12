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

namespace Splash\Local\Objects\Product\Variants;

use Splash\Core\SplashCore      as Splash;
use WC_Product;

/**
 * WooCommerce Product Variants Attributes Data Access
 */
trait AttributesTrait
{
    /**
     * Build Product Attribute Definition Array
     *
     * @param WC_Product $product Product Object
     *
     * @return array
     */
    public function getProductAttributesArray($product)
    {
        $result =   array();

        if (empty($product->get_parent_id())) {
            return $result;
        }

        foreach ($product->get_attributes() as $key => $attribute) {
            $key    = str_replace('pa_', '', $key);
            //====================================================================//
            // Add Attribute Value to Definition Array
            $result[$key]   =   $attribute;
        }

        return $result;
    }

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Attributes Fields using FieldFactory
     */
    private function buildVariantsAttributesFields()
    {
        $this->fieldsFactory()->setDefaultLanguage(self::getDefaultLanguage());
        $groupName  =  __("Variations");

        //====================================================================//
        // Product Variation List - Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("code")
            ->Name(__("Code"))
            ->InList("attributes")
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "VariantAttributeCode")
            ->isNotTested();

        //====================================================================//
        // Product Variation List - Variation Attribute Name
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("name")
                ->Name(__("Name"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "VariantAttributeName")
                ->setMultilang($isoCode)
                ->InList("attributes")
                ->isNotTested();
        }
        
        //====================================================================//
        // Product Variation List - Variation Attribute Value
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("value")
                ->Name(__("Value"))
                ->Group($groupName)
                ->MicroData("http://schema.org/Product", "VariantAttributeValue")
                ->setMultilang($isoCode)
                ->InList("attributes")
                ->isNotTested();
        }
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getVariantsAttributesFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "attributes", $fieldName);
        if (!$fieldId) {
            return;
        }
        if (!$this->isVariantsProduct()) {
            unset($this->in[$key]);

            return;
        }

        //====================================================================//
        // READ Fields
        foreach ($this->product->get_attributes() as $code => $name) {
            //====================================================================//
            // Load Attribute Group
            $groupId        =   $this->getAttributeGroupByCode($code);
            $group          =   wc_get_attribute($groupId);
            //====================================================================//
            // Load Attribute
            $attributeId    =   $this->getAttributeByCode($code, $name);
            $attribute      =   get_term($attributeId);
            $attributeName  =   isset($attribute->name) ? $attribute->name : null;

            $value  =   null;
            //====================================================================//
            // Read Monolang Values
            switch ($fieldId) {
                case 'code':
                    $value  =   str_replace('pa_', '', $code);

                    break;
            }
            //====================================================================//
            // Read Multilang Values
            foreach (self::getAvailableLanguages() as $isoCode) {
                //====================================================================//
                // Reduce Multilang Field Name
                $baseFieldName = self::getMultilangFieldName($fieldId, $isoCode);
                //====================================================================//
                // Read Field Value
                switch ($baseFieldName) {
                    case 'name':
                        $value  =   $this->encodeMultilang($group->name, $isoCode);

                        break;
                    case 'value':
                        $value  =   $this->encodeMultilang($attributeName, $isoCode);

                        break;
                }
            }
            
            self::lists()->insert($this->out, "attributes", $fieldId, $code, $value);
        }
        unset($this->in[$key]);
        //====================================================================//
        // Sort Attributes by Code
        ksort($this->out["attributes"]);
    }

    //====================================================================//
    // CRUD Functions
    //====================================================================//

    /**
     * Check if Attribute Array is Valid for Writing
     *
     * @param mixed $attrData Attribute Array
     *
     * @return bool
     */
    private function isValidAttributeDefinition($attrData)
    {
        //====================================================================//
        // Check Attribute is Array
        if ((!is_array($attrData) && !is_a($attrData, "ArrayObject")) || empty($attrData)) {
            return false;
        }
        //====================================================================//
        // Check Attributes Code is Given
        if (!isset($attrData["code"]) || !is_string($attrData["code"]) || empty($attrData["code"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Code is Not Valid."
            );
        }
        //====================================================================//
        // Check Attributes Names are Given
        if (!isset($attrData["name"]) || !is_scalar($attrData["name"]) || empty($attrData["name"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Public Name is Not Valid."
            );
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (!isset($attrData["value"]) || !is_scalar($attrData["value"]) || empty($attrData["value"])) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Product Attribute Value Name is Not Valid."
            );
        }

        return $this->isValidMonolangAttributeDefinition($attrData);
    }
    
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setVariantsAttributesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Safety Check
        if ("attributes" !== $fieldName) {
            return;
        }

        //====================================================================//
        // Update Products Attributes Ids
        $newAttributes  =   array();
        foreach ($fieldData as $item) {
            //====================================================================//
            // Check Product Attributes are Valid
            if (!$this->isValidAttributeDefinition($item)) {
                continue;
            }
            //====================================================================//
            // Extract Attribute Informations
            $code   =   $item["code"];
            $name   =   $item["name"];
            $value  =   $item["value"];
            //====================================================================//
            // Identify or Add Attribute Group Id
            $attributeGroupId   =   $this->getVariantsAttributeGroup($code, $name);
            if (!$attributeGroupId) {
                continue;
            }
            //====================================================================//
            // Identify or Add Attribute Id
            $attributeId   =   $this->getVariantsAttributeValue($code, $value);
            if (!$attributeId) {
                continue;
            }
            //====================================================================//
            // Load Attribute Class
            $attribute  =   get_term($attributeId);
            $newAttributes[wc_attribute_taxonomy_name($code)] = $attribute->slug;
        }
        
        //====================================================================//
        // Update Combination if Modified
        if ($this->product->get_attributes() != $newAttributes) {
            foreach ($newAttributes as $key => $value) {
                $this->setPostMeta("attribute_" . $key, $value);
            }
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Ensure Product Attribute Group Exists
     *
     * @param string $code Attribute Group Code
     * @param string $name Attribute Group Name
     *
     * @return false|int
     */
    private function getVariantsAttributeGroup($code, $name)
    {
        //====================================================================//
        // Load Product Attribute Group
        $attributeGroupId   =   $this->getAttributeGroupByCode($code);
        if (!$attributeGroupId) {
            //====================================================================//
            // Add Product Attribute Group
            $attributeGroupId = $this->addAttributeGroup($code, $name);
        }
        //====================================================================//
        // DEBUG MODE => Update Group Names
        if (defined("SPLASH_DEBUG") && !empty(SPLASH_DEBUG)) {
            wc_update_attribute($attributeGroupId, array(
                "slug"  =>   $code,
                "name"  =>   $this->decodeMultilang($name, self::getDefaultLanguage())
            ));
        }
        //====================================================================//
        // An Error Occured
        if (!$attributeGroupId) {
            return false;
        }
        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        $this->assignAttributeGroup($this->baseProduct, $attributeGroupId, $code);

        return $attributeGroupId;
    }

    /**
     * Ensure Product Attribute Group Exists
     *
     * @param string $slug  Attribute Group Slug
     * @param string $value Attribute Value
     *
     * @return false|int
     */
    private function getVariantsAttributeValue($slug, $value)
    {
        //====================================================================//
        // Load Product Attribute Value
        $attributeId   =   $this->getAttributeByName($slug, $value);
        if (!$attributeId) {
            //====================================================================//
            // Add Product Attribute Value
            $attributeId = $this->addAttributeValue($slug, $value);
        }
        if (!$attributeId) {
            return false;
        }

        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        $this->assignAttribute($this->baseProduct, $slug, $attributeId);

        return $attributeId;
    }
}
