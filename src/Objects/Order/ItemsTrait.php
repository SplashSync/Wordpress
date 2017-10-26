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

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;

use WC_Order_Item_Product;

/**
 * @abstract    WooCommerce Order Items Data Access
 */
trait ItemsTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildItemsFields()   {

        $GroupName  =   __("Items");
        
        //====================================================================//
        // Order Line Description
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("name")
                ->InList("items")
                ->Name( __("Item"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/partOfInvoice","description")        
                ->Association("name@items","quantity@items","subtotal@items");        

        //====================================================================//
        // Order Line Product Identifier
        $this->FieldsFactory()->Create(self::Objects()->Encode( "Product" , SPL_T_ID))        
                ->Identifier("product")
                ->InList("items")
                ->Name( __("Product"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product","productID")
                ->Association("name@items","quantity@items","subtotal@items")
                ->NotTested();        
//                ;        

        //====================================================================//
        // Order Line Quantity
        $this->FieldsFactory()->Create(SPL_T_INT)        
                ->Identifier("quantity")
                ->InList("items")
                ->Name( __("Quantity"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/QuantitativeValue","value")        
                ->Association("name@items","quantity@items","subtotal@items");        

        //====================================================================//
        // Order Line Discount
        $this->FieldsFactory()->Create(SPL_T_DOUBLE)        
                ->Identifier("discount")
                ->InList("items")
                ->Name( __("Percentage discount"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Order","discount")
                ->Association("name@items","quantity@items","subtotal@items");        

        //====================================================================//
        // Order Line Unit Price
        $this->FieldsFactory()->Create(SPL_T_PRICE)        
                ->Identifier("subtotal")
                ->InList("items")
                ->Name( __("Price"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/PriceSpecification","price")        
                ->Association("name@items","quantity@items","subtotal@items");        
        
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
    private function getItemsFields($Key,$FieldName)
    {
        // Check if List field & Init List Array
        $FieldId = self::Lists()->InitOutput( $this->Out, "items", $FieldName );
        if ( !$FieldId ) {
            return;
        } 
               
        
        foreach ( $this->loadAllItems() as $Index => $Item ) {
            if ( is_a( $Item , "WC_Order_Item_Product" )) {
                $Data   =   $this->getProductItemData($Item,$FieldId);
            } else {
                $Data   =   $this->getItemData($Item,$FieldId);
            }
            //====================================================================//
            // Insert Data in List
            self::Lists()->Insert( $this->Out, "items", $FieldName, $Index, $Data );
        }
        
        unset($this->In[$Key]);
    }
       
    /**
     *  @abstract     Read Order Item Field
     * 
     *  @return         none
     */
    private function getProductItemData($Item,$FieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldId)
        {
            case 'name':
                return   $Item->get_name();
                
            case 'quantity':
                return   $Item->get_quantity();
                
            case 'price':
                return $this->EncodePrice($Item->get_total(), $Item->get_total_tax(), $Item->get_quantity());

            case 'discount':
                // Compute Discount (Precent of Total to SubTotal)
                return   (double) ( 100 * ( $Item->get_subtotal() - $Item->get_total() ) / $Item->get_subtotal());
                
            case 'subtotal':
                return $this->EncodePrice($Item->get_subtotal(), $Item->get_subtotal_tax(), $Item->get_quantity());
                
            case 'product':
                if ( ! $Item->get_product_id() ) {
                    return Null;      
                }
                if ( $Item->get_variation_id() ) {
                    return   self::Objects()->Encode( "Product" , $Item->get_variation_id());
                }
                return   self::Objects()->Encode( "Product" , $Item->get_product_id());

        }
        return Null;      
    }
    
    /**
     *  @abstract     Read Order Item Field
     * 
     *  @return         none
     */
    private function getItemData($Item,$FieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldId)
        {
            case 'name':
                return   $Item->get_name();
                
            case 'quantity':
                return   1;
                
            case 'price':
            case 'subtotal':
                return $this->EncodePrice($Item->get_total(), $Item->get_total_tax(), 1);

            case 'discount':
                // Compute Discount (Precent of Total to SubTotal)
                return   (double) 0;
                
            case 'product':
                return Null; 

        }
        return Null;      
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
    private function setItemsFields($FieldName,$Data) 
    {
        // Check if List field
        if ( $FieldName != "items" ) {
            return;
        } 
        
        // Load Initial Version
        $this->loadAllItems();
        
        foreach ( $Data as $ItemData )
        {
            $this->Item    =   array_shift( $this->Items );
            //====================================================================//
            // Create Item If Needed
            if ( ! $this->Item ) {
                $this->Item = new WC_Order_Item_Product();
                $this->Object->add_item($this->Item);
            }
            //====================================================================//
            // Update Item
            if ( is_a( $this->Item , "WC_Order_Item_Product" )) {
                $this->setProductItem($ItemData);
            } else {
                $this->setItem($ItemData);
            }
            

        }
        
        foreach ( $this->Items as $Item )
        {
            $this->Object->remove_item($Item);
        }        
        
        unset($this->In["items"]);
    }

    /**
     *  @abstract     Write Given Product Item Fields
     * 
     *  @param        mixed     $Data               Field Data
     * 
     *  @return         none
     */
    private function setProductItem($Data) 
    {
        //====================================================================//
        // Update Quantity
        if ( isset($Data["quantity"]) ) {
            $this->setGeneric("_quantity" , $Data["quantity"], "Item");
        } 
        //====================================================================//
        // Update Name
        if ( isset($Data["name"]) ) {
            $this->setGeneric("_name" , $Data["name"], "Item");
        } 
        //====================================================================//
        // Update Product Id
        if ( isset($Data["product"]) ) {
            $ProductId = self::Objects()->Id($Data["product"]);
            $this->setGeneric("_product_id" , $ProductId, "Item");
        } 
        //====================================================================//
        // Update Unit Price
        if ( isset($Data["subtotal"]) ) {
            // Compute Expected Subtotal
            $Subtotal       = $this->Item->get_quantity() * self::Prices()->TaxExcluded($Data["subtotal"]);
            // Compute Expected Subtotal Tax Incl.
            $Subtotal_tax   = $this->Item->get_quantity() * self::Prices()->TaxAmount($Data["subtotal"]);
        } else {
            $Subtotal       = $this->Item->get_subtotal();
            $Subtotal_tax   = $this->Item->get_subtotal_tax();
        }
        //====================================================================//
        // Update Total Line Price
        // There is A Discount Percent
        if ( isset($Data["discount"]) ) {
            // Compute Expected Total
            $Total       = $Subtotal        * ( 1 - $Data["discount"] / 100);
            // Compute Expected Total Tax Incl.
            $Total_tax   = $Subtotal_tax    * ( 1 - $Data["discount"] / 100);
        // There is NO Discount
        } else {
            $Total       = $Subtotal;
            $Total_tax   = $Subtotal_tax;
        } 
        //====================================================================//
        // Update Item Taxes
        if( $Total_tax != $this->Item->get_total_tax()) {
            $this->setItemTaxArray('total', $Total_tax); 
        }
        if( $Subtotal_tax != $this->Item->get_subtotal_tax()) {
            $this->setItemTaxArray('subtotal', $Subtotal_tax); 
        }
        //====================================================================//
        // Update Item Totals
        $this->setGeneric("_total" ,        $Total,         "Item");
        $this->setGeneric("_subtotal" ,     $Subtotal,      "Item");
    }
    
    /**
     *  @abstract     Write Given Item Fields
     * 
     *  @param        mixed     $Data               Field Data
     * 
     *  @return         none
     */
    private function setItem($Data) 
    {
        //====================================================================//
        // Update Name
        if ( isset($Data["name"]) ) {
            $this->setGeneric("_name" , $Data["name"], "Item");
        } 
        //====================================================================//
        // Update Quantity
        if ( isset($Data["quantity"]) ) {
            $Qty    =   $Data["quantity"];
        } else {
            $Qty    =   1;
        } 
        //====================================================================//
        // Update Unit Price
        if ( isset($Data["subtotal"]) ) {
            // Compute Expected Total
            $Total       = $Qty * self::Prices()->TaxExcluded($Data["subtotal"]);
            // Compute Expected Total Tax Incl.
            $Total_tax   = $Qty * self::Prices()->TaxPercent($Data["subtotal"]);
        // There is NO Discount
        } else {
            $Total       = $this->Item->get_total();
            $Total_tax   = $this->Item->get_total_tax();
        } 
        //====================================================================//
        // Update Item Taxes
        if( $Total_tax != $this->Item->get_total_tax()) {
            $this->setItemTaxArray('total', $Total_tax); 
        }
        //====================================================================//
        // Update Item Totals
        $this->setGeneric("_total" ,        $Total,         "Item");
    }
    
    /**
     *  @abstract     Write Given Tax Amount to Tax Array Row
     * 
     *  @param        mixed     $Data               Field Data
     * 
     *  @return         none
     */
    private function setItemTaxArray($Row, $Amount) 
    {
        $Taxes = $this->Item->get_taxes();
        if ( empty($Taxes[$Row]) ) {
            $Taxes[$Row] = [ 0 => $Amount ];
        } else {
            foreach ( $Taxes[$Row] as &$Value) {
                $Value      = $Amount;
                $Amount  = 0;
            }
        }
        $this->Item->set_taxes($Taxes);        
        $this->needUpdate();
    }  
    
    /**
     *  @abstract     Load All Order Items
     */
    private function loadAllItems() 
    {
        $this->Items    =   array_merge(
                $this->Object->get_items(),
                $this->Object->get_items("shipping"),
                $this->Object->get_items("fee")
            );
        
        return $this->Items;
    }
    
    /*
     * @abstract    ENcode Price with Tax Mode detection
     */
    private function EncodePrice( $Amount, $TaxAmount, $Quantity = 1 ) {
        $TotalHT    =   (double) ($Amount / $Quantity);
        $TotalTTC   =   Null;
        $VAT        =   (double) ($Amount  ? (100 * $TaxAmount / $Amount) : 0);
        return   self::Prices()
            ->Encode(
                $TotalHT,                               // Tax Excl.
                $VAT,                                   // VAT
                $TotalTTC,                              // Tax Incl.
                get_woocommerce_currency(),             // Currency
                get_woocommerce_currency_symbol()       // Symbol
            );
    }
}
