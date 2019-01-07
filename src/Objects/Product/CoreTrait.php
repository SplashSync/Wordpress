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
     *   @abstract     Build Core Fields using FieldFactory
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Detect Multilangual Mode
        if ($this->multilangMode() != self::$MULTILANG_DISABLED) {
            $varcharType    = SPL_T_MVARCHAR;
            $textType       = SPL_T_MTEXT;
        } else {
            $varcharType    = SPL_T_VARCHAR;
            $textType       = SPL_T_TEXT;
        }
        
        //====================================================================//
        // Title
        $this->fieldsFactory()->Create($varcharType)
            ->Identifier("post_title")
            ->Name(__("Title"))
            ->Description(__("Products") . " : " . __("Title"))
            ->MicroData("http://schema.org/Product", "name")
            ->isLogged()
            ->isReadOnly()
            ->isListed()
            ;

        //====================================================================//
        // Title without Options
        $this->fieldsFactory()->Create($varcharType)
            ->Identifier("base_title")
            ->Name(__("Base Title"))
            ->Group("Meta")
            ->Description(__("Products") . " : " . __("Title without Options"))
            ->MicroData("http://schema.org/Product", "alternateName")
            ->isRequired()
            ;
        
        //====================================================================//
        // Slug
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_name")
            ->Name(__("Slug"))
            ->Description(__("Products") . " : " . __("Permalink"))
            ->MicroData("http://schema.org/Product", "urlRewrite")
            ->isNotTested()    // Only Due to LowerCase Convertion
            ->isLogged()
            ;
        
        //====================================================================//
        // Contents
        $this->fieldsFactory()->Create($textType)
            ->Identifier("post_content")
            ->Name(__("Contents"))
            ->Description(__("Products") . " : " . __("Contents"))
            ->MicroData("http://schema.org/Article", "articleBody")
            ->isLogged()
            ;
        
        //====================================================================//
        // Status
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_status")
            ->Name(__("Status"))
            ->Description(__("Products") . " : " . __("Status"))
            ->MicroData("http://schema.org/Article", "status")
            ->AddChoices(get_post_statuses())
            ->isListed()
            ;
        
        //====================================================================//
        // Short Description
        $this->fieldsFactory()->Create($varcharType)
            ->Identifier("post_excerpt")
            ->Name(__("Product short description"))
            ->Description(__("Products") . " : " . __("Product short description"))
            ->MicroData("http://schema.org/Product", "description");
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
            case 'post_title':
//                //====================================================================//
//                // TODO => With WpMultilang, Titles are not Translated on Variation Posts
//                //====================================================================//
                $this->getMultilangual($fieldName);

                break;
            case 'base_title':
                //====================================================================//
                // Detect Product Variation
                if ($this->isVariantsProduct()) {
                    $this->object->{$fieldName}    =  get_post($this->product->get_parent_id())->post_title;
                } else {
                    $this->object->{$fieldName}    =  $this->object->post_title;
                }
                $this->getMultilangual($fieldName);

                break;
            case 'post_content':
            case 'post_excerpt':
                //====================================================================//
                // Detect Product Variation
                if ($this->isVariantsProduct()) {
                    $this->object->{$fieldName}    =  get_post($this->product->get_parent_id())->{$fieldName};
                }
                $this->getMultilangual($fieldName);

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
            case 'post_title':
                $this->setMultilangual($fieldName, $fieldData);

                break;
            case 'base_title':
                if ($this->isVariantsProduct()) {
                    $this->setSimple(
                        "post_title",
                        $this->decodeMultilang($fieldData, $this->baseProduct->get_name()),
                        "baseObject"
                    );

                    break;
                }
                $this->setMultilangual('post_title', $fieldData);

                break;
            case 'post_content':
            case 'post_excerpt':
                if ($this->isVariantsProduct()) {
                    $this->setMultilangual($fieldName, $fieldData, "baseObject");

                    break;
                }
                $this->setMultilangual($fieldName, $fieldData);

                break;
            default:
                return;
        }
        
        unset($this->in[$fieldName]);
    }
}
