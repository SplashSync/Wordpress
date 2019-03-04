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
 * Wordpress Taximony Data Access
 */
trait TaxTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build TAx Fields using FieldFactory
     */
    private function buildTaxFields()
    {
        //====================================================================//
        // TAXIMONY
        //====================================================================//

        //====================================================================//
        // Parent Object
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Page", SPL_T_ID))
            ->Identifier("post_parent")
            ->Name(__("Parent"))
            ->MicroData("http://schema.org/Article", "mainEntity");
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
    private function getTaxFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'post_parent':
                if (!$this->object->post_parent) {
                    $this->out[$fieldName] = 0;

                    break;
                }
                $this->out[$fieldName] = self::objects()->Encode("Page", $this->object->post_parent);

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
    private function setTaxFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'post_parent':
                $postId = (int) self::objects()->Id($fieldData);
                $this->setSimple($fieldName, (get_post($postId) ? $postId : 0));

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
