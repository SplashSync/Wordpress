<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product\Variants;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\AttributesManager as Manager;
use WC_Product;
use WP_Term;

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
        $result = array();

        if (empty($product->get_parent_id())) {
            return $result;
        }

        foreach ($product->get_attributes() as $key => $attribute) {
            $key = str_replace('pa_', '', $key);
            //====================================================================//
            // Add Attribute Value to Definition Array
            $result[$key] = $attribute;
        }

        return $result;
    }

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Attributes Fields using FieldFactory
     *
     * @return void
     */
    protected function buildVariantsAttributesFields()
    {
        $this->fieldsFactory()->setDefaultLanguage(self::getDefaultLanguage());
        $groupName = __("Variations");

        //====================================================================//
        // Product Variation List - Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("code")
            ->Name(__("Code"))
            ->InList("attributes")
            ->Group($groupName)
            ->addOption("isLowerCase", true)
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
     */
    protected function getVariantsAttributesFields($key, $fieldName)
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
            // Read Attributes Values
            $value = $this->getVariantsAttributesField($fieldId, $code, $name);
            //====================================================================//
            // Add Attributes Value to List
            self::lists()->insert($this->out, "attributes", $fieldId, $code, $value);
        }

        unset($this->in[$key]);
        //====================================================================//
        // Sort Attributes by Code
        ksort($this->out["attributes"]);
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
    protected function setVariantsAttributesFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Safety Check
        if ("attributes" !== $fieldName) {
            return;
        }

        //====================================================================//
        // Update Products Attributes Ids
        $newAttributes = array();
        foreach ($fieldData as $item) {
            //====================================================================//
            // Check Product Attributes are Valid
            if (!$this->isValidAttributeDefinition($item)) {
                continue;
            }
            //====================================================================//
            // Extract Attribute Informations
            $code = $item["code"];
            $value = $item["value"];
            //====================================================================//
            // Identify or Add Attribute Group Id
            $names = self::buildMultilangArray($item, "name");
            if (false == $this->touchAttributeGroup($code, $names)) {
                continue;
            }
            //====================================================================//
            // Identify or Add Attribute Id
            $attribute = $this->touchAttributeValue($code, $value, $item);
            if (!$attribute) {
                continue;
            }
            //====================================================================//
            // Load Attribute Class
            $newAttributes[wc_attribute_taxonomy_name($code)] = $attribute->slug;
        }

        //====================================================================//
        // Update Combination if Modified
        if ($this->product->get_attributes() != $newAttributes) {
            foreach ($newAttributes as $key => $value) {
                $this->setPostMeta("attribute_".$key, $value);
            }
        }
        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Privates Methods
    //====================================================================//

    /**
     * Read requested Attribute Field
     *
     * @param string $fieldId Field Identifier / Name
     * @param string $code    Attribute Group Code
     * @param string $name    Attribute Name/Code
     *
     * @return null|array|string
     */
    private function getVariantsAttributesField($fieldId, $code, $name)
    {
        //====================================================================//
        // Load Attribute Group
        $group = Manager::getGroupByCode($code);
        if (!$group) {
            return $this->getVariantsCustomAttributesField($fieldId, $code, $name);
        }
        //====================================================================//
        // Load Attribute
        $attribute = Manager::getValueByCode($code, $name);
        $attributeName = isset($attribute->name) ? $attribute->name : "";

        //====================================================================//
        // Read Monolang Values
        switch ($fieldId) {
            case 'code':
                return str_replace('pa_', '', $code);
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
                    return $this->encodeMultilang($group->name, $isoCode);
                case 'value':
                    return $this->encodeMultilang($attributeName, $isoCode);
            }
        }

        return null;
    }

    /**
     * Read requested Custom Attribute Field
     *
     * @param string $fieldId Field Identifier / Name
     * @param string $code    Attribute Group Code
     * @param string $name    Attribute Name/Code
     *
     * @return null|array|string
     */
    private function getVariantsCustomAttributesField($fieldId, $code, $name)
    {
        //====================================================================//
        // Read Monolang Values
        switch ($fieldId) {
            case 'code':
                return $code;
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
                    return Manager::getGroupNameFromParent($this->baseProduct, $code);
                case 'value':
                    return $name;
            }
        }

        return null;
    }

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
            return Splash::log()->errTrace("Product Attribute Code is Not Valid.");
        }

        return $this->isValidMonolangAttributeDefinition($attrData);
    }

    /**
     * Check if Attribute Array is Valid Monolangual Attribute Definition
     *
     * @param array|ArrayObject $attrData Attribute Array
     *
     * @return bool
     */
    private function isValidMonolangAttributeDefinition($attrData)
    {
        //====================================================================//
        // Check Attributes Names are Given
        if (!isset($attrData["name"]) || !is_scalar($attrData["name"]) || empty($attrData["name"])) {
            return Splash::log()->errTrace("Product Attribute Public Name is Not Valid.");
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (!isset($attrData["value"]) || !is_scalar($attrData["value"]) || empty($attrData["value"])) {
            return Splash::log()->errTrace("Product Attribute Value Name is Not Valid.");
        }

        return true;
    }

    /**
     * Ensure Product Attribute Group Exists
     *
     * @param string $code  Attribute Group Code
     * @param array  $names Attribute Group Names
     *
     * @return bool
     */
    private function touchAttributeGroup($code, $names)
    {
        //====================================================================//
        // Load Product Attribute Group
        $attributeGroup = Manager::getGroupByCode($code);
        if (!$attributeGroup) {
            //====================================================================//
            // Add Product Attribute Group
            $attributeGroup = Manager::addGroup($code, $names);
        }
        //====================================================================//
        // An Error Occured
        if (!$attributeGroup) {
            return false;
        }
        //====================================================================//
        // DEBUG MODE => Update Group Names
        if (defined("SPLASH_DEBUG") && !empty(SPLASH_DEBUG)) {
            Manager::updateGroup($attributeGroup, $names);
        }
        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        Manager::assignGroupToProduct($this->baseProduct, $attributeGroup, $code);

        return true;
    }

    /**
     * Ensure Product Attribute Group Exists
     *
     * @param string            $slug  Attribute Group Slug
     * @param string            $value Attribute Value
     * @param array|ArrayObject $item  Complete Attribute Definition
     *
     * @return false|WP_Term
     */
    private function touchAttributeValue($slug, $value, $item = array())
    {
        //====================================================================//
        // Load Product Attribute Value
        $attribute = Manager::getValueByName($slug, $value);
        if (!$attribute) {
            //====================================================================//
            // Add Product Attribute Value
            $attribute = Manager::addValue($slug, self::buildMultilangArray($item, "value"));
        }
        if (!$attribute) {
            return false;
        }

        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        Manager::assignValue($this->baseProduct, $slug, $attribute->term_id);

        return $attribute;
    }
}
