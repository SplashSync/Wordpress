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
 * WordPress Core Data Access
 */
trait MainTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Main Fields using FieldFactory
     *
     * @return void
     */
    private function buildMainFields()
    {
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("_sku")
            ->name(__("SKU"))
            ->description(__("Product")." : ".__("SKU"))
            ->isListed()
            ->microData("http://schema.org/Product", "model")
            ->isRequired()
            ->isPrimary()
        ;
        //====================================================================//
        // Active => Product Is Visible in Catalog
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("is_visible")
            ->name(__("Enabled"))
            ->description(__("Product")." : ".__("Enabled"))
            ->microData("http://schema.org/Product", "offered")
        ;

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        $groupName = __("Shipping");

        //====================================================================//
        // Weight
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("_weight")
            ->name(__("Weight"))
            ->description(__("Product")." ".__("Weight"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "weight")
        ;
        //====================================================================//
        // Height
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("_height")
            ->name(__("Height"))
            ->description(__("Product")." ".__("Height"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "height")
        ;
        //====================================================================//
        // Depth
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("_length")
            ->name(__("Length"))
            ->description(__("Product")." ".__("Length"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "depth")
        ;
        //====================================================================//
        // Width
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("_width")
            ->name(__("Width"))
            ->description(__("Product")." ".__("Width"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "width")
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
    private function getMainFields(string $key, string $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_sku':
                $this->getPostMeta($fieldName);

                break;
            case '_length':
            case '_width':
            case '_height':
                $this->getPostMetaLength($fieldName);

                break;
            case '_weight':
                $this->getPostMetaWeight($fieldName);

                break;
            case 'is_visible':
                $this->out[$fieldName] = ("private" !== $this->object->post_status);

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
     * @param string                $fieldName Field Identifier / Name
     * @param bool|float|int|string $fieldData Field Data
     *
     * @return void
     */
    private function setMainFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case '_sku':
                $this->setPostMeta($fieldName, $fieldData);

                break;
            case '_length':
            case '_width':
            case '_height':
                $this->setPostMetaLength($fieldName, (float) $fieldData);

                break;
            case '_weight':
                $this->setPostMetaWeight($fieldName, (float) $fieldData);

                break;
            case 'is_visible':
                $this->setSimple("post_status", $fieldData ? "publish" : "private");

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
