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

namespace Splash\Local\Objects\Post;

/**
 * WordPress Core Data Access
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
        //====================================================================//
        // Title
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("post_title")
            ->name(__("Title"))
            ->description(__("Post")." : ".__("Title"))
            ->microData("http://schema.org/Article", "name")
            ->isRequired()
            ->isLogged()
            ->isListed()
        ;
        //====================================================================//
        // Slug
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("post_name")
            ->name(__("Slug"))
            ->description(__("Post")." : ".__("Permalink"))
            ->microData("http://schema.org/Article", "identifier")
            ->addOption("isLowerCase")
            ->isListed()
            ->isLogged()
        ;
        //====================================================================//
        // Contents
        $this->fieldsFactory()->create(SPL_T_TEXT)
            ->identifier("post_content")
            ->name(__("Contents"))
            ->description(__("Post")." : ".__("Contents"))
            ->microData("http://schema.org/Article", "articleBody")
            ->isLogged()
        ;
        //====================================================================//
        // Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("post_status")
            ->name(__("Status"))
            ->description(__("Post")." : ".__("Status"))
            ->microData("http://schema.org/Article", "status")
            ->addChoices(get_post_statuses())
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
