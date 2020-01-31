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
trait StockTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Stock Fields using FieldFactory
     *
     * @return void
     */
    private function buildStockFields()
    {
        $groupName = __("Inventory");

        //====================================================================//
        // PRODUCT STOCKS
        //====================================================================//

        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->Identifier("_stock")
            ->Name(__("Stock quantity"))
            ->Description(__("Product")." ".__("Stock quantity"))
            ->MicroData("http://schema.org/Offer", "inventoryLevel")
            ->Group($groupName)
            ->isListed();

        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("outofstock")
            ->Name(__("Out of stock"))
            ->Description(__("Product")." ".__("Out of stock"))
            ->MicroData("http://schema.org/ItemAvailability", "OutOfStock")
            ->Group($groupName)
            ->isReadOnly();
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
    private function getStockFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_stock':
                $this->out[$fieldName] = (int) get_post_meta($this->object->ID, $fieldName, true);

                break;
            case 'outofstock':
                $this->out[$fieldName] = (get_post_meta($this->object->ID, "_stock", true) ? false : true);

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
    private function setStockFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case '_stock':
                $wcProduct = wc_get_product($this->object->ID);
                if (!$wcProduct) {
                    break;
                }

                if ($wcProduct->get_stock_quantity() != $fieldData) {
                    $this->setPostMeta($fieldName, $fieldData);
                    wc_update_product_stock($wcProduct, $fieldData);
                    $this->setPostMeta("_manage_stock", "yes");
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
