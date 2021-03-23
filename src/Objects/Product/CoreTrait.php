<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    private function buildCoreFields()
    {
        Splash::log()->www("Mode", self::multilangMode());

        $this->fieldsFactory()->setDefaultLanguage(self::getDefaultLanguage());

        //====================================================================//
        // Title
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("post_title")
                ->Name(__("Title"))
                ->Description(__("Products")." : ".__("Title"))
                ->MicroData("http://schema.org/Product", "name")
                ->setMultilang($isoCode)
                ->isLogged()
                ->isListed(self::isDefaultLanguage($isoCode))
                ->isReadOnly();
        }

        //====================================================================//
        // Title without Options
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("base_title")
                ->Name(__("Base Title"))
                ->Group("Meta")
                ->Description(__("Products")." : ".__("Title without Options"))
                ->MicroData("http://schema.org/Product", "alternateName")
                ->setMultilang($isoCode)
                ->isRequired(self::isDefaultLanguage($isoCode))
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }

        //====================================================================//
        // Short Description
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("post_excerpt")
                ->Name(__("Product short description"))
                ->Description(__("Products")." : ".__("Product short description"))
                ->MicroData("http://schema.org/Product", "description")
                ->setMultilang($isoCode)
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }

        //====================================================================//
        // Contents
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->Create(SPL_T_TEXT)
                ->Identifier("post_content")
                ->Name(__("Contents"))
                ->Description(__("Products")." : ".__("Contents"))
                ->MicroData("http://schema.org/Article", "articleBody")
                ->setMultilang($isoCode)
                ->isLogged()
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }

        //====================================================================//
        // Slug
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_name")
            ->Name(__("Slug"))
            ->Description(__("Products")." : ".__("Permalink"))
            ->MicroData("http://schema.org/Product", "urlRewrite")
            ->isNotTested()    // Only Due to LowerCase Conversion
            ->addOption("isLowerCase", true)
            ->isLogged();

        //====================================================================//
        // Status
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_status")
            ->Name(__("Status"))
            ->Description(__("Products")." : ".__("Status"))
            ->MicroData("http://schema.org/Article", "status")
            ->AddChoices(get_post_statuses())
            ->isNotTested()
            ->isListed();
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
    private function getCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'post_name':
            case 'post_status':
                $this->getSimple($fieldName);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getCoreMultiLangFields($key, $fieldName)
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->getCoreMultiLangField($key, $fieldName, $isoCode);
        }
    }

    /**
     * Read requested Mulltilang Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     * @param string $isoCode   Language Iso Code
     *
     * @return void
     */
    private function getCoreMultiLangField($key, $fieldName, $isoCode)
    {
        //====================================================================//
        // Reduce Multi-lang Field Name
        $baseFieldName = (string) self::getMultiLangFieldName($fieldName, $isoCode);

        //====================================================================//
        // READ Fields
        switch ($baseFieldName) {
            case 'post_title':
                $this->getMultilangual($baseFieldName, $isoCode);

                break;
            case 'base_title':
                //====================================================================//
                // Detect Product Variation
                if ($this->isVariantsProduct()) {
                    $this->object->{$baseFieldName} = $this->baseObject->post_title;
                } else {
                    $this->object->{$baseFieldName} = $this->object->post_title;
                }
                //====================================================================//
                // Read Product Multi-lang Data
                $this->getMultilangual($baseFieldName, $isoCode);

                break;
            case 'post_content':
            case 'post_excerpt':
                //====================================================================//
                // Detect Product Variation
                $source = $this->isVariantsProduct() ? "baseObject" : "object";
                //====================================================================//
                // Read Product Multi-lang Data
                $this->getMultilangual($baseFieldName, $isoCode, $source);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
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
    private function setCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Fullname Writtings
            case 'post_name':
            case 'post_status':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setCoreMultilangFields($fieldName, $fieldData)
    {
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->setCoreMultilangField($fieldName, $fieldData, $isoCode);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     * @param string $isoCode   Language Iso Code
     *
     * @return void
     */
    private function setCoreMultilangField($fieldName, $fieldData, $isoCode)
    {
        //====================================================================//
        // Reduce Multilang Field Name
        $baseFieldName = (string) self::getMultiLangFieldName($fieldName, $isoCode);

        //====================================================================//
        // WRITE Field
        switch ($baseFieldName) {
            case 'post_title':
                $this->setMultilangual($baseFieldName, $isoCode, $fieldData);

                break;
            case 'base_title':
                if ($this->isVariantsProduct()) {
                    $baseTitle = $this->decodeMultilang($fieldData, $isoCode, $this->baseProduct->get_name());
                    if (empty($baseTitle)) {
                        break;
                    }
                    $this->setSimple("post_title", $baseTitle, "baseObject");
                    $this->baseProduct->set_name($baseTitle);

                    break;
                }
                $this->setMultilangual('post_title', $isoCode, $fieldData);

                break;
            case 'post_content':
            case 'post_excerpt':
                //====================================================================//
                // Detect Product Variation
                $source = $this->isVariantsProduct() ? "baseObject" : "object";
                //====================================================================//
                // Write Product Multilang Data
                $this->setMultilangual($baseFieldName, $isoCode, $fieldData, $source);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
