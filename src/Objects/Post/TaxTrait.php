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
 * WordPress Tax Data Access
 */
trait TaxTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Tax Fields using FieldFactory
     *
     * @return void
     */
    private function buildTaxFields()
    {
        //====================================================================//
        // Parent Object
        $this->fieldsFactory()->create((string) self::objects()->encode("Page", SPL_T_ID))
            ->identifier("post_parent")
            ->name(__("Parent"))
            ->microData("http://schema.org/Article", "mainEntity")
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
    private function getTaxFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'post_parent':
                if (!$this->object->post_parent) {
                    $this->out[$fieldName] = 0;

                    break;
                }
                $this->out[$fieldName] = self::objects()
                    ->encode("Page", (string) $this->object->post_parent)
                ;

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
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     *
     * @return void
     */
    private function setTaxFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'post_parent':
                $postId = (int) self::objects()->id((string) $fieldData);
                $this->setSimple($fieldName, (get_post($postId) ? $postId : 0));

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
