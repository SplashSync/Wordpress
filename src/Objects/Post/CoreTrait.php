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

namespace Splash\Local\Objects\Post;

/**
 * Wordpress Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Core Fields using FieldFactory
     */
    private function buildCoreFields()
    {
        //====================================================================//
        // Title
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_title")
            ->Name(__("Title"))
            ->Description(__("Post")." : ".__("Title"))
            ->MicroData("http://schema.org/Article", "name")
            ->isRequired()
            ->isLogged()
            ->isListed()
            ;

        //====================================================================//
        // Slug
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_name")
            ->Name(__("Slug"))
            ->Description(__("Post")." : ".__("Permalink"))
            ->MicroData("http://schema.org/Article", "identifier")
            ->addOption("isLowerCase")
            ->isListed()
            ->isLogged()
            ;

        //====================================================================//
        // Contents
        $this->fieldsFactory()->Create(SPL_T_TEXT)
            ->Identifier("post_content")
            ->Name(__("Contents"))
            ->Description(__("Post")." : ".__("Contents"))
            ->MicroData("http://schema.org/Article", "articleBody")
            ->isLogged()
            ;

        //====================================================================//
        // Status
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_status")
            ->Name(__("Status"))
            ->Description(__("Post")." : ".__("Status"))
            ->MicroData("http://schema.org/Article", "status")
            ->AddChoices(get_post_statuses())
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
     */
    private function getCoreFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'post_name':
            case 'post_title':
            case 'post_content':
            case 'post_status':
                $this->getSimple($fieldName);

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
     */
    private function setCoreFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            //====================================================================//
            // Fullname Writtings
            case 'post_name':
            case 'post_title':
            case 'post_content':
            case 'post_status':
                $this->setSimple($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
