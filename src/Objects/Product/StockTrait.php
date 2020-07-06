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
        // Stock is Managed
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("stock_managed")
            ->Name(__("Stock Managed"))
            ->Description(__("Product")." ".__("Manage stock?"))
            ->MicroData("http://schema.org/Product", "stockIsManaged")
            ->Group($groupName);

        //====================================================================//
        // Stock is Managed at Parent level
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("stock_from_parent")
            ->Name(__("Stock from Parent"))
            ->Description(__("Product")." ".__("Enable stock management at product level"))
            ->MicroData("http://schema.org/Product", "stockFromParent")
            ->Group($groupName)
        ;

        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("outofstock")
            ->Name(__("Out of stock"))
            ->Description(__("Product")." ".__("Out of stock"))
            ->MicroData("http://schema.org/ItemAvailability", "OutOfStock")
            ->Group($groupName)
            ->isReadOnly()
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
    private function getStockFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_stock':
                $stockManagerId = $this->product->get_stock_managed_by_id();
                $this->out[$fieldName] = (int) get_post_meta($stockManagerId, $fieldName, true);

                break;
            case 'outofstock':
                $stockManagerId = $this->product->get_stock_managed_by_id();
                $this->out[$fieldName] = (get_post_meta($stockManagerId, "_stock", true) ? false : true);

                break;
            case 'stock_managed':
                $this->out[$fieldName] = !empty($this->product->get_manage_stock());

                break;
            case 'stock_from_parent':
                $stockManagerId = $this->product->get_stock_managed_by_id();
                $this->out[$fieldName] = ($stockManagerId != $this->product->get_id());

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
                $stockManagerId = $this->product->get_stock_managed_by_id();
                if ($stockManagerId != $this->product->get_id()) {
                    // Force Writing of Stock Even if Store Do Not Manage Stocks
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
    }    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setStockMetaFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'stock_managed':
                $stockManaged = $this->product->get_manage_stock();
                if ($stockManaged != $fieldData) {
                    $this->product->set_manage_stock((bool) $fieldData);
                }

                break;
            case 'stock_from_parent':
                $stockFromParent = ("parent" === $this->product->get_manage_stock());
                if ($stockFromParent != $fieldData) {
                    if ($fieldData) {
                        $this->product->set_manage_stock(false);
                    }
                    $this->baseProduct->set_manage_stock((bool) $fieldData);
                    $this->baseProduct->save();
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
