<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
    public function getProductAttributesArray($product): array
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
    protected function buildVariantsAttributesFields(): void
    {
        $this->fieldsFactory()->setDefaultLanguage(self::getDefaultLanguage());
        $groupName = __("Variations");

        //====================================================================//
        // Product Variation List - Variation Attribute Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("code")
            ->name(__("Code"))
            ->inList("attributes")
            ->group($groupName)
            ->addOption("isLowerCase", true)
            ->microData("http://schema.org/Product", "VariantAttributeCode")
            ->isNotTested();

        //====================================================================//
        // Product Variation List - Variation Attribute Name
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("name")
                ->name(__("Name"))
                ->group($groupName)
                ->microData("http://schema.org/Product", "VariantAttributeName")
                ->setMultilang($isoCode)
                ->inList("attributes")
                ->isReadOnly(!self::isWritableLanguage($isoCode))
                ->isNotTested();
        }

        //====================================================================//
        // Product Variation List - Variation Attribute Value
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("value")
                ->name(__("Value"))
                ->group($groupName)
                ->microData("http://schema.org/Product", "VariantAttributeValue")
                ->setMultilang($isoCode)
                ->inList("attributes")
                ->isReadOnly(!self::isWritableLanguage($isoCode))
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
    protected function getVariantsAttributesFields(string $key, string $fieldName): void
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
        if (is_array($this->out["attributes"])) {
            ksort($this->out["attributes"]);
        }
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setVariantsAttributesFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Safety Check
        if ("attributes" !== $fieldName) {
            return;
        }
        //====================================================================//
        // Update Products Attributes Ids
        $newAttributes = array();
        $fieldData = is_iterable($fieldData) ? $fieldData : array();
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
            if (!$this->touchAttributeGroup($code, $names)) {
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
    private function getVariantsAttributesField(string $fieldId, string $code, string $name)
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
        $attributeName = $attribute->name ?? "";

        //====================================================================//
        // Read Mono-lang Values
        switch ($fieldId) {
            case 'code':
                return str_replace('pa_', '', $code);
        }

        //====================================================================//
        // Read Multi-lang Values
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Reduce Multi-lang Field Name
            $baseFieldName = (string) self::getMultiLangFieldName($fieldId, $isoCode);
            //====================================================================//
            // Read Field Value
            switch ($baseFieldName) {
                case 'name':
                    return $this->encodeMultiLang($group->name, $isoCode);
                case 'value':
                    return $this->encodeMultiLang($attributeName, $isoCode);
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
     * @return null|string
     */
    private function getVariantsCustomAttributesField(string $fieldId, string $code, string $name): ?string
    {
        //====================================================================//
        // Read Mono-lang Values
        if ('code' == $fieldId) {
            return $code;
        }
        //====================================================================//
        // Read Multi-lang Values
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Reduce Multi-lang Field Name
            $baseFieldName = (string) self::getMultiLangFieldName($fieldId, $isoCode);
            //====================================================================//
            // Read Field Value
            switch ($baseFieldName) {
                case 'name':
                    return isset($this->baseProduct)
                        ? Manager::getGroupNameFromParent($this->baseProduct, $code)
                        : null
                    ;
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
    private function isValidAttributeDefinition($attrData): bool
    {
        //====================================================================//
        // Check Attribute is Array
        if ((!is_array($attrData) && !($attrData instanceof ArrayObject)) || empty($attrData)) {
            return false;
        }
        //====================================================================//
        // Check Attributes Code is Given
        if (!isset($attrData["code"]) || !is_string($attrData["code"]) || empty($attrData["code"])) {
            return Splash::log()->errTrace("Product Attribute Code is Not Valid.");
        }

        return $this->isValidMonoLangAttributeDefinition($attrData);
    }

    /**
     * Check if Attribute Array is Valid Mono-Lang Attribute Definition
     *
     * @param array|ArrayObject $attrData Attribute Array
     *
     * @return bool
     */
    private function isValidMonoLangAttributeDefinition($attrData): bool
    {
        //====================================================================//
        // Check Attributes Names are Given
        if (empty($attrData["name"]) || !is_scalar($attrData["name"])) {
            return Splash::log()->errTrace("Product Attribute Public Name is Not Valid.");
        }
        //====================================================================//
        // Check Attributes Values are Given
        if (empty($attrData["value"]) || !is_scalar($attrData["value"])) {
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
    private function touchAttributeGroup(string $code, array $names): bool
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
        // An Error Occurred
        if (!$attributeGroup) {
            return false;
        }
        //====================================================================//
        // DEBUG MODE => Update Group Names
        if (Splash::isDebugMode()) {
            Manager::updateGroup($attributeGroup, $names);
        }
        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        if (isset($this->baseProduct)) {
            Manager::assignGroupToProduct($this->baseProduct, $attributeGroup, $code);
        }

        return true;
    }

    /**
     * Ensure Product Attribute Group Exists
     *
     * @param string            $slug  Attribute Group Slug
     * @param string            $value Attribute Value
     * @param array|ArrayObject $item  Complete Attribute Definition
     *
     * @return null|WP_Term
     */
    private function touchAttributeValue(string $slug, string $value, $item = array()): ?WP_Term
    {
        //====================================================================//
        // Load Product Attribute Value
        $attribute = Manager::getValueByName($slug, $value);
        if (!$attribute) {
            //====================================================================//
            // Add Product Attribute Value
            $attribute = Manager::addValue($slug, self::buildMultilangArray($item, "value"));
        }
        if (!$attribute || empty($this->baseProduct)) {
            return null;
        }

        //====================================================================//
        // Ensure this Attribute Group is assigned to product
        Manager::assignValue($this->baseProduct, $slug, $attribute->term_id);

        return $attribute;
    }
}
