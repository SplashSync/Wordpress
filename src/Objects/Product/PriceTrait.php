<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use WC_Cache_Helper;
use WC_Tax;

/**
 * WooCommerce Product Price Data Access
 */
trait PriceTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Price Fields using FieldFactory
     */
    private function buildPriceFields()
    {
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//
        
        //====================================================================//
        // Product Selling Price
        $this->fieldsFactory()->Create(SPL_T_PRICE)
            ->Identifier("_regular_price")
            ->Name(__("Regular price"))
            ->Description(__("Product") . " " . __("Regular price"))
            ->MicroData("http://schema.org/Product", "price")
            ->isListed();
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
    private function getPriceFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_regular_price':
                //====================================================================//
                // Read Regular Price
                if (wc_prices_include_tax()) {
                    $priceTTC   = (double)  $this->product->get_regular_price();
                    $priceHT   = null;
                } else {
                    $priceHT    = (double)  $this->product->get_regular_price();
                    $priceTTC   = null;
                }
                $taxRate    =   $this->getPriceBaseTaxRate();
                //====================================================================//
                // Build Price Array
                $this->out[$fieldName] = self::prices()->Encode(
                    $priceHT,
                    $taxRate,
                    $priceTTC,
                    get_woocommerce_currency(),
                    get_woocommerce_currency_symbol()
                );

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
    private function setPriceFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case '_price':
                //====================================================================//
                // Write Regular Price
                $newPrice = wc_prices_include_tax()
                    ? self::prices()->TaxIncluded($fieldData)
                    : self::prices()->TaxExcluded($fieldData);
                $this->setPostMeta($fieldName, $newPrice);

                break;
            case '_regular_price':
                //====================================================================//
                // Write Regular Price
                $newPrice = wc_prices_include_tax()
                    ? self::prices()->TaxIncluded($fieldData)
                    : self::prices()->TaxExcluded($fieldData);
                $this->product->set_regular_price((string) $newPrice);
                //====================================================================//
                // Write Tax Class
                $taxClass   =   $this->identifyPriceTaxClass(self::prices()->TaxPercent($fieldData));
                $this->product->set_tax_class($taxClass);
                $this->product->save();

                break;
            default:
                return;
        }
        
        unset($this->in[$fieldName]);
    }
    
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     * Identify Base Tax Rate
     *
     * @return double
     */
    private function getPriceBaseTaxRate()
    {
        if (!$this->product->is_taxable()) {
            return (double)  0;
        }
        $taxRates   =   WC_Tax::get_base_tax_rates($this->product->get_tax_class());
        if (!is_array($taxRates)) {
            return (double)  0;
        }
        $taxArray   =   array_shift($taxRates);
        if (!is_array($taxArray)) {
            return (double)  0;
        }

        return (double) $taxArray["rate"];
    }
    
    /**
     * Identify Base Tax Class
     *
     * @param mixed $taxPercent
     *
     * @return string
     */
    private function identifyPriceTaxClass($taxPercent = 0)
    {
        // Select Standard Tax Class
        $rates  =   WC_Tax::get_rates_for_tax_class("");
        if (!is_array($rates)) {
            return "";
        }
        $std    =   array_shift($rates);
        $code   =   "standard";
        $rate   =   !empty($std) ? $std->tax_rate : 0;

        // For Each Additionnal Tax Class
        foreach (WC_Tax::get_tax_classes() as $class) {
            // Load Tax Rate
            $taxRates  =   WC_Tax::get_rates_for_tax_class(sanitize_title($class));
            if (!is_array($taxRates)) {
                continue;
            }            
            $current   =    array_shift($taxRates);
            if (is_null($current)) {
                continue;
            }
            if (abs($taxPercent - $current->tax_rate) <  abs($taxPercent - $rate)) {
                $code   =   $current->tax_rate_class;
                $rate   =   $current->tax_rate;
            }
        }
        
        return $code;
    }
}
