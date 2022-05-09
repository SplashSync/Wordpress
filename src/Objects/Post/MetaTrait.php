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
        // Author
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("post_author")
            ->Name(__("Author"))
            ->Group("Meta")
            ->MicroData("http://schema.org/Article", "author")
            ->isReadOnly();

        //====================================================================//
        // TRACEABILITY INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Last Modification Date
        $this->fieldsFactory()->Create(SPL_T_DATETIME)
            ->Identifier("post_modified")
            ->Name(__("Last Modified"))
            ->Group("Meta")
            ->MicroData("http://schema.org/DataFeedItem", "dateModified")
            ->isReadOnly();

        //====================================================================//
        // Creation Date
        $this->fieldsFactory()->Create(SPL_T_DATETIME)
            ->Identifier("post_date")
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
            case 'post_date':
            case 'post_modified':
                $this->getSimple($fieldName);

                break;
            case 'post_author':
                $user = get_user_by("ID", $this->object->post_author);
                if (!$this->object->post_author || empty($user)) {
                    $this->out[$fieldName] = "";

                    break;
                }
                $this->out[$fieldName] = $user->display_name;

                break;
            case 'splash_id':
            case 'splash_origin':
                $this->getPostMeta($fieldName);

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
            case 'post_date':
            case 'post_modified':
                $this->setSimple($fieldName, $fieldData);

                break;
            case 'splash_id':
            case 'splash_origin':
                $this->setPostMeta($fieldName, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
