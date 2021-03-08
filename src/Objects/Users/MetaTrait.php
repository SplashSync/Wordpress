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

namespace Splash\Local\Objects\Users;

/**
 * Wordpress Core Data Access
 */
trait MetaTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Meta Fields using FieldFactory
     *
     * @return void
     */
    private function buildMetaFields()
    {
        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->Create(SPL_T_DATETIME)
            ->Identifier("user_registered")
            ->Name(__("Created"))
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly();

        //====================================================================//
        // SPLASH RESERVED INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Splash Unique Object Id
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("splash_id")
            ->Name("Splash Id")
            ->Group("Meta")
            ->MicroData("http://splashync.com/schemas", "ObjectId");

        //====================================================================//
        // Splash Object SOrigin Node Id
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("splash_origin")
            ->Name("Splash Origin Node")
            ->Group("Meta")
            ->MicroData("http://splashync.com/schemas", "SourceNodeId");
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
    private function getMetaFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'user_registered':
            case 'splash_id':
            case 'splash_origin':
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
     *
     * @return void
     */
    private function setMetaFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'user_registered':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'splash_id':
            case 'splash_origin':
                $this->setUserMeta($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
