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

namespace Splash\Local\Objects\Product;

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
    protected function buildCoreFields(): void
    {
        $this->fieldsFactory()->setDefaultLanguage(self::getDefaultLanguage());
        //====================================================================//
        // Title
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("post_title")
                ->name(__("Title"))
                ->description(__("Products")." : ".__("Title"))
                ->microData("http://schema.org/Product", "name")
                ->setMultilang($isoCode)
                ->isLogged()
                ->isListed(self::isDefaultLanguage($isoCode))
                ->isReadOnly()
            ;
        }
        //====================================================================//
        // Title without Options
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("base_title")
                ->name(__("Base Title"))
                ->group("Meta")
                ->description(__("Products")." : ".__("Title without Options"))
                ->microData("http://schema.org/Product", "alternateName")
                ->setMultilang($isoCode)
                ->isRequired(self::isDefaultLanguage($isoCode))
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }
        //====================================================================//
        // Short Description
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("post_excerpt")
                ->name(__("Product short description"))
                ->description(__("Products")." : ".__("Product short description"))
                ->microData("http://schema.org/Product", "description")
                ->setMultilang($isoCode)
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }
        //====================================================================//
        // Contents
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->fieldsFactory()->create(SPL_T_TEXT)
                ->identifier("post_content")
                ->name(__("Contents"))
                ->description(__("Products")." : ".__("Contents"))
                ->microData("http://schema.org/Article", "articleBody")
                ->setMultilang($isoCode)
                ->isLogged()
                ->isReadOnly(!self::isWritableLanguage($isoCode))
            ;
        }
        //====================================================================//
        // Slug
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("post_name")
            ->name(__("Slug"))
            ->description(__("Products")." : ".__("Permalink"))
            ->microData("http://schema.org/Product", "urlRewrite")
            ->isNotTested()    // Only Due to LowerCase Conversion
            ->addOption("isLowerCase", true)
            ->isLogged()
        ;
        //====================================================================//
        // Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("post_status")
            ->name(__("Status"))
            ->description(__("Products")." : ".__("Status"))
            ->microData("http://schema.org/Article", "status")
            ->addChoices(get_post_statuses())
            ->isNotTested()
            ->isListed()
        ;
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
    protected function getCoreFields(string $key, string $fieldName): void
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
    protected function getCoreMultiLangFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->getCoreMultiLangField($key, $fieldName, $isoCode);
        }
    }

    /**
     * Read requested Multi-lang Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     * @param string $isoCode   Language Iso Code
     *
     * @return void
     */
    protected function getCoreMultiLangField(string $key, string $fieldName, string $isoCode): void
    {
        //====================================================================//
        // Reduce Multi-lang Field Name
        $baseFieldName = (string) self::getMultiLangFieldName($fieldName, $isoCode);

        //====================================================================//
        // READ Fields
        switch ($baseFieldName) {
            case 'post_title':
                $this->getMultiLang($baseFieldName, $isoCode);

                break;
            case 'base_title':
                //====================================================================//
                // Detect Product Variation
                if ($this->isVariantsProduct()) {
                    /** @phpstan-ignore-next-line  */
                    $this->object->{$baseFieldName} = $this->baseObject->post_title;
                } else {
                    /** @phpstan-ignore-next-line  */
                    $this->object->{$baseFieldName} = $this->object->post_title;
                }
                //====================================================================//
                // Read Product Multi-lang Data
                $this->getMultiLang($baseFieldName, $isoCode);

                break;
            case 'post_content':
            case 'post_excerpt':
                //====================================================================//
                // Detect Product Variation
                $source = $this->isVariantsProduct() ? "baseObject" : "object";
                //====================================================================//
                // Read Product Multi-lang Data
                $this->getMultiLang($baseFieldName, $isoCode, $source);

                break;
            default:
                return;
        }

        unset($this->in[$key]);
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
    protected function setCoreFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Full Name Writings
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
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreMultiLangFields(string $fieldName, $fieldData): void
    {
        foreach (self::getAvailableLanguages() as $isoCode) {
            $this->setCoreMultilangField($fieldName, $fieldData, $isoCode);
        }
    }

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     * @param string      $isoCode   Language Iso Code
     *
     * @return void
     */
    protected function setCoreMultiLangField(string $fieldName, $fieldData, string $isoCode): void
    {
        //====================================================================//
        // Reduce Multi-lang Field Name
        $baseFieldName = (string) self::getMultiLangFieldName($fieldName, $isoCode);

        //====================================================================//
        // WRITE Field
        switch ($baseFieldName) {
            case 'post_title':
                $this->setMultiLang($baseFieldName, $isoCode, (string) $fieldData);

                break;
            case 'base_title':
                if ($this->isVariantsProduct() && $this->baseProduct) {
                    $baseTitle = $this->decodeMultiLang((string) $fieldData, $isoCode, $this->baseProduct->get_name());
                    if (empty($baseTitle)) {
                        break;
                    }
                    $this->setSimple("post_title", $baseTitle, "baseObject");
                    $this->baseProduct->set_name($baseTitle);

                    break;
                }
                $this->setMultiLang('post_title', $isoCode, (string) $fieldData);

                break;
            case 'post_content':
            case 'post_excerpt':
                //====================================================================//
                // Detect Product Variation
                $source = $this->isVariantsProduct() ? "baseObject" : "object";
                //====================================================================//
                // Write Product Multi-lang Data
                $this->setMultiLang($baseFieldName, $isoCode, (string) $fieldData, $source);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
