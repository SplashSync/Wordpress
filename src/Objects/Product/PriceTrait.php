<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * 
 **/

namespace Splash\Local\Objects\Product;

use WC_Tax;

/**
 * WooCommerce Product Price Data Access
 */
trait PriceTrait {
    
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
//                ->Identifier("_price")
//                ->Name( __("Price") )
//                ->Description( __("Product") . " " . __("Price") )
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
            case '_price':
                //====================================================================//
                // Read Regular Price
                if ( wc_prices_include_tax() ) {
                    $PriceTTC   = (double)  $this->Product->get_price();
                    $PriceHT   = Null;
                } else {
                    $PriceHT    = (double)  $this->Product->get_price();
                    $PriceTTC   = Null;
                }
                $Tax    =   $this->getPriceBaseTaxRate();
                //====================================================================//
                // Build Price Array
                $this->Out[$FieldName] = self::Prices()->Encode(
                        $PriceHT,$Tax,$PriceTTC,
                        get_woocommerce_currency(), 
                        get_woocommerce_currency_symbol(),
                        NULL);
                
                break;
                
            case '_regular_price':
                //====================================================================//
                // Read Regular Price
                if ( wc_prices_include_tax()   ) {
                    $PriceTTC   = (double)  $this->Product->get_regular_price();
                    $PriceHT   = Null;
                } else {
                    $PriceHT    = (double)  $this->Product->get_regular_price();
                    $PriceTTC   = Null;
                }
                $Tax    =   $this->getPriceBaseTaxRate();
                //====================================================================//
                // Build Price Array
                $this->Out[$FieldName] = self::Prices()->Encode(
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
            case '_price':
                //====================================================================//
                // Write Regular Price
                $NewPrice = wc_prices_include_tax() ? self::Prices()->TaxIncluded($Data) : self::Prices()->TaxExcluded($Data); 
                $this->setPostMeta($FieldName,$NewPrice);
                break;

            case '_regular_price':
                //====================================================================//
                // Write Regular Price
                $NewPrice = wc_prices_include_tax() ? self::Prices()->TaxIncluded($Data) : self::Prices()->TaxExcluded($Data); 
                $this->setPostMeta($FieldName,$NewPrice);
                //====================================================================//
                // Write Tax Class
                $TaxClass   =   $this->identifyPriceTaxClass( self::Prices()->TaxPercent($Data) );
//                $this->setPostMeta("tax_class",$TaxClass);
                $this->Product->set_tax_class($TaxClass);
                $this->Product->Save();
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
    
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     *  @abstract     Identify Base Tax Rate
     * 
     *  @return         none
     */
    private function getPriceBaseTaxRate() 
    {
        if ( !$this->Product->is_taxable() ) {
            return (double)  0;
        } 
        $TaxArray   =   array_shift(WC_Tax::get_base_tax_rates( $this->Product->get_tax_class() ) );
        if (!is_array($TaxArray)) {
            return (double)  0;
        }
        return (double) $TaxArray["rate"];
    }    
    
    /**
     *  @abstract     Identify Base Tax Class
     * 
     *  @return         none
     */
    private function identifyPriceTaxClass( $Tax_Percent = 0 ) 
    {
        // Select Standard Tax Class
        $Std    =   array_shift( WC_Tax::get_rates_for_tax_class(""));
        $Code   =   "standard";
        $Rate   =   $Std->tax_rate;

        // For Each Additionnal Tax Class
        foreach (WC_Tax::get_tax_classes() as $class) {
            
            $Current   =    array_shift( WC_Tax::get_rates_for_tax_class( sanitize_title( $class ) ));
            
            if (is_null($Current)) {
                continue;
            }
            
            if ( abs($Tax_Percent - $Current->tax_rate) <  abs($Tax_Percent - $Rate) ) {
                $Code   =   $Current->tax_rate_class;
                $Rate   =   $Current->tax_rate;
            } 

        }  
        
        return $Code;
    }       
    
}
