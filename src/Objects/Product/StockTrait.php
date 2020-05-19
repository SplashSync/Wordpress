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
                $stockManagerid = $this->product->get_stock_managed_by_id();
                $this->out[$fieldName] = (int) get_post_meta($stockManagerid, $fieldName, true);

                break;
            case 'outofstock':
                $stockManagerid = $this->product->get_stock_managed_by_id();
                $this->out[$fieldName] = (get_post_meta($stockManagerid, "_stock", true) ? false : true);

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
                //====================================================================//
                // Load Product & Verify Stock if Changed
                $wcProduct = wc_get_product($this->object->ID);
                if (!$wcProduct || ($wcProduct->get_stock_quantity() == $fieldData)) {
                    break;
                }
                //====================================================================//
                // Stock is Stored at Parent Product Level
                if ("parent" == $this->product->get_manage_stock()) {
                    // Force Writing of Stok Even if Store Do Not Manage Stocks
                    get_post_meta($wcProduct->get_parent_id(), "_stock", $fieldData);
                    wc_update_product_stock($wcProduct, $fieldData);

                    break;
                }
                //====================================================================//
                // If Stock Above 0 (Defined) => Force Manage Stock Option
                if ($fieldData) {
                    $this->setPostMeta("_manage_stock", "yes");
                }
                //====================================================================//
                // Force Writing of Stock Even if Store Do Not Manage Stocks
                $this->setPostMeta($fieldName, $fieldData);
                wc_update_product_stock($wcProduct, $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
