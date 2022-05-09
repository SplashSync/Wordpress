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
            ->identifier("_stock")
            ->name(__("Stock quantity"))
            ->description(__("Product")." ".__("Stock quantity"))
            ->microData("http://schema.org/Offer", "inventoryLevel")
            ->group($groupName)
            ->isListed()
        ;
        //====================================================================//
        // Stock is Managed
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("stock_managed")
            ->name(__("Stock Managed"))
            ->description(__("Product")." ".__("Manage stock?"))
            ->microData("http://schema.org/Product", "stockIsManaged")
            ->group($groupName)
        ;
        //====================================================================//
        // Stock is Managed at Parent level
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("stock_from_parent")
            ->name(__("Stock from Parent"))
            ->description(__("Product")." ".__("Enable stock management at product level"))
            ->microData("http://schema.org/Product", "stockFromParent")
            ->group($groupName)
            ->isNotTested()
        ;
        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->identifier("outofstock")
            ->name(__("Out of stock"))
            ->description(__("Product")." ".__("Out of stock"))
            ->microData("http://schema.org/ItemAvailability", "OutOfStock")
            ->group($groupName)
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
    private function getStockFields(string $key, string $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_stock':
                $stockManagerId = $this->product->get_stock_managed_by_id();
                // @phpstan-ignore-next-line
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
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param int    $fieldData Field Data
     *
     * @return void
     */
    private function setStockFields(string $fieldName, $fieldData)
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
    }

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setStockMetaFields(string $fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'stock_managed':
                $stockManaged = $this->product->get_manage_stock();
                if ($stockManaged != $fieldData) {
                    $this->setPostMeta("_manage_stock", $fieldData ? "yes" : "no");
                }

                break;
            case 'stock_from_parent':
                if (!isset($this->baseProduct)) {
                    break;
                }
                $stockFromParent = ($this->product->get_stock_managed_by_id() != $this->product->get_id());
                if ($stockFromParent != $fieldData) {
                    if ($fieldData) {
                        $this->setPostMeta("_manage_stock", "no");
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
