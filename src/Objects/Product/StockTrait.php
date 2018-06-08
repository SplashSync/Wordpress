<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Product;

/**
 * @abstract    Wordpress Core Data Access
 */
trait StockTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Stock Fields using FieldFactory
    */
    private function buildStockFields()
    {
        
        $GroupName  = __("Inventory");
        
        //====================================================================//
        // PRODUCT STOCKS
        //====================================================================//
        
        //====================================================================//
        // Stock Reel
        $this->fieldsFactory()->Create(SPL_T_INT)
                ->Identifier("_stock")
                ->Name(__("Stock quantity"))
                ->Description(__("Product") . " " . __("Stock quantity"))
                ->MicroData("http://schema.org/Offer", "inventoryLevel")
                ->Group($GroupName)
                ->isListed();

        //====================================================================//
        // Out of Stock Flag
        $this->fieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("outofstock")
                ->Name(__("Out of stock"))
                ->Description(__("Product") . " " . __("Out of stock"))
                ->MicroData("http://schema.org/ItemAvailability", "OutOfStock")
                ->Group($GroupName)
                ->isReadOnly();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    private function getStockFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case '_stock':
                $this->Out[$FieldName] = (int) get_post_meta($this->Object->ID, $FieldName, true);
                break;
            
            case 'outofstock':
                $this->Out[$FieldName] = (get_post_meta($this->Object->ID, "_stock", true) ? false : true);
                break;
            
            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
        
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return         none
     */
    private function setStockFields($FieldName, $Data)
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case '_stock':
                $Product = wc_get_product($this->Object->ID);
                if (!$Product) {
                    break;
                }
                
                if ($Product->get_stock_quantity() != $Data) {
                    $this->setPostMeta($FieldName, $Data);
                    wc_update_product_stock($Product, $Data);
                }
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
}
