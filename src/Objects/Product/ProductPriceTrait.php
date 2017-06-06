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

use WC_Tax;

/**
 * Wordpress Core Data Access
 */
trait ProductPriceTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Price Fields using FieldFactory
    */
    private function buildPriceFields()   {
        
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//
        
        //====================================================================//
        // Product Selling Price
        $this->FieldsFactory()->Create(SPL_T_PRICE)
                ->Identifier("_regular_price")
                ->Name( __("Regular price") )
                ->Description( __("Product") . " " . __("Regular price") )
                ->MicroData("http://schema.org/Product","price")
                ->isListed();
        
//        //====================================================================//
//        // Product Selling Base Price
//        $this->FieldsFactory()->Create(SPL_T_PRICE)
//                ->Identifier("price-base")
//                ->Name(Translate::getAdminTranslation("Price (tax excl.)", "AdminProducts") . " Base (" . $this->Currency->sign . ")")
//                ->MicroData("http://schema.org/Product","basePrice")
//                ->isListed();
        
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
    private function getPriceFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case '_regular_price':
//                $this->Out[$FieldName] = get_post_meta( $this->Object->ID, $FieldName, True );
                
                $Product = get_product($this->Object->ID);
                
                //====================================================================//
                // Read Regular Price
                if ( wc_prices_include_tax()   ) {
                    $PriceTTC   = (double)  $Product->get_regular_price();
                    $PriceHT   = Null;
                } else {
                    $PriceHT    = (double)  $Product->get_regular_price();
                    $PriceTTC   = Null;
                }
                if ( $Product->is_taxable() ) {
                    $Tax        = (double)  WC_Tax::get_rate_percent( $Product->get_tax_class() );
                } else {
                    $Tax        = (double)  0;
                }
                
                //====================================================================//
                // Build Price Array
                $this->Out[$FieldName] = self::Price_Encode(
                        $PriceHT,$Tax,$PriceTTC,
                        get_woocommerce_currency(), 
                        get_woocommerce_currency_symbol(),
                        NULL);
                
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
    private function setPriceFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            case '_regular_price':
                
                //====================================================================//
                // Write Regular Price
                $NewPrice = wc_prices_include_tax() ? $Data["ttc"] : $Data["ht"]; 
                if (get_post_meta( $this->Object->ID, $FieldName, True ) != $NewPrice) {
                    update_post_meta( $this->Object->ID, $FieldName, $NewPrice );
                    $this->update = True;
                } 
                
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
}
