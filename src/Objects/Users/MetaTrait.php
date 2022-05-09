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

namespace Splash\Local\Objects\Users;

/**
 * WordPress Core Data Access
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
    private function buildMetaFields(): void
    {
        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("user_registered")
            ->name(__("Created"))
            ->group("Meta")
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly()
        ;

        //====================================================================//
        // SPLASH RESERVED INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Splash Unique Object Id
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("splash_id")
            ->name("Splash Id")
            ->group("Meta")
            ->microData("http://splashync.com/schemas", "ObjectId")
        ;
        //====================================================================//
        // Splash Object SOrigin Node Id
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("splash_origin")
            ->name("Splash Origin Node")
            ->group("Meta")
            ->microData("http://splashync.com/schemas", "SourceNodeId")
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
    private function getMetaFields(string $key, string $fieldName): void
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
    private function setMetaFields(string $fieldName, $fieldData): void
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
