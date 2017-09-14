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

/**
 * @abstract    WooCommerce Order Status Data Access
 */
trait StatusTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildStatusFields()   {

        //====================================================================//
        // Order Current Status
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("status")
                ->Name(_("Status"))
                ->Group(__("Status"))
                ->MicroData("http://schema.org/Order","orderStatus")
//                ->AddChoices(wc_get_order_statuses())
                ->AddChoice("OrderCanceled",    __("Cancelled"))
                ->AddChoice("OrderDraft",       __("Pending payment"))
                ->AddChoice("OrderProcessing",  __("Processing"))
                ->AddChoice("OrderDelivered",   __("Completed"))                
//                ->NotTested()
                ;
        
        //====================================================================//
        // Is Draft
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("isdraft")
                ->Group(__("Status"))
                ->Name(__("Order") . " : " . __("Pending payment"))
                ->MicroData("http://schema.org/OrderStatus","OrderDraft")
                ->Association( "isdraft","iscanceled","isvalidated","isclosed")
                ->ReadOnly();     

        //====================================================================//
        // Is Canceled
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("iscanceled")
                ->Group(__("Status"))
                ->Name(__("Order") . " : " . __("Cancelled"))
                ->MicroData("http://schema.org/OrderStatus","OrderCancelled")
                ->Association( "isdraft","iscanceled","isvalidated","isclosed")
                ->ReadOnly();     
        
        //====================================================================//
        // Is Validated
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("isvalidated")
                ->Group(__("Status"))
                ->Name(__("Order") . " : " . __("Processing"))
                ->MicroData("http://schema.org/OrderStatus","OrderProcessing")
                ->Association( "isdraft","iscanceled","isvalidated","isclosed")
                ->ReadOnly();
        
        //====================================================================//
        // Is Closed
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("isclosed")
                ->Name(__("Order") . " : " . __("Completed"))
                ->Group(__("Status"))
                ->MicroData("http://schema.org/OrderStatus","OrderDelivered")
                ->Association( "isdraft","iscanceled","isvalidated","isclosed")
                ->ReadOnly();

        //====================================================================//
        // Is Paid
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("ispaid")
                ->Name(__("Order") . " : " . __("Paid"))
                ->Group(__("Status"))
                ->MicroData("http://schema.org/PaymentStatusType","PaymentComplete")
                ->readOnly();
       
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
    private function getStatusFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case 'status':
                $this->Out[$FieldName] = $this->encodeStatus();
                break;
            
            case 'isdraft':
                $this->Out[$FieldName]  = in_array( $this->Object->get_status() , ["pending"]);
                break;
            case 'iscanceled':
                $this->Out[$FieldName]  = in_array( $this->Object->get_status() , ["canceled", "refunded", "failed"]);
                break;
            case 'isvalidated':
                $this->Out[$FieldName]  = in_array( $this->Object->get_status() , ["processing", "on-hold", "wc-awaiting-shipment", "wc-shipped"]);
                break;
            case 'isclosed':
                $this->Out[$FieldName]  = in_array( $this->Object->get_status() , ["completed"]);
                break;    
            case 'ispaid':
                $this->Out[$FieldName]  = in_array( $this->Object->get_status() , ["processing", "on-hold", "completed", "wc-awaiting-shipment", "wc-shipped"]);
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
    private function setStatusFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            case 'status':
                if ( $this->encodeStatus() != $Data ) {
                    $this->Object->set_status( $this->decodeStatus($Data), "Updated by Splash!" , True );
                } 
                break;            
            
            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
    //====================================================================//
    // Order Status Convertion
    //====================================================================//
    
    private function encodeStatus() 
    {
        switch ($this->Object->get_status()) {
            case 'pending':
                return "OrderDraft";
                
            case 'processing':
            case 'on-hold':
            case 'wc-awaiting-shipment':
            case 'wc-shipped':
                return "OrderProcessing";
                
            case 'completed':
                return "OrderDelivered";
                
            case 'cancelled':
            case 'refunded':
            case 'failed':
                return "OrderCanceled";
        }
        return "Unknown";
    }
    
    
    private function decodeStatus($Status) 
    {
        switch ($Status) {
           case 'OrderDraft':
               return "pending";
               
           case 'OrderProcessing':
           case 'OrderInTransit':
               return "processing";
               
           case 'OrderDelivered':
               return "completed";
               
           case 'OrderCanceled':
               return "cancelled";
        }       
        return Null;
    }    
}
