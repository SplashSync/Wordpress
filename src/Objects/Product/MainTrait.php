<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
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
 * Wordpress Core Data Access
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
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("_sku")
            ->Name(__("SKU"))
            ->Description(__("Product")." : ".__("SKU"))
            ->isListed()
            ->MicroData("http://schema.org/Product", "model")
            ->isRequired();

        //====================================================================//
        // Active => Product Is Visible in Catalog
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("is_visible")
            ->Name(__("Enabled"))
            ->Description(__("Product")." : ".__("Enabled"))
            ->MicroData("http://schema.org/Product", "offered");

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        $groupName = __("Shipping");

        //====================================================================//
        // Weight
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("_weight")
            ->Name(__("Weight"))
            ->Description(__("Product")." ".__("Weight"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "weight");

        //====================================================================//
        // Height
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("_height")
            ->Name(__("Height"))
            ->Description(__("Product")." ".__("Height"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "height");

        //====================================================================//
        // Depth
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("_length")
            ->Name(__("Length"))
            ->Description(__("Product")." ".__("Length"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "depth");

        //====================================================================//
        // Width
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("_width")
            ->Name(__("Width"))
            ->Description(__("Product")." ".__("Width"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "width");
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
    private function getMainFields($key, $fieldName)
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
                $this->getPostMetaLenght($fieldName);

                break;
            case '_weight':
                $this->getPostMetaWheight($fieldName);

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
    private function setMainFields($fieldName, $fieldData)
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
                $this->setPostMetaLenght($fieldName, $fieldData);

                break;
            case '_weight':
                $this->setPostMetaWheight($fieldName, $fieldData);

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
