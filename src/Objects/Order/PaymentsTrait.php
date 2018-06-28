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
 * @abstract    WooCommerce Order Payment Data Access
 */
trait PaymentsTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildPaymentsFields()
    {

        $GroupName  =   __("Payments");
        
        //====================================================================//
        // Payment Line Payment Method
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier('_payment_method')
                ->InList("payments")
                ->Group($GroupName)
                ->Name(__("Method"))
                ->MicroData("http://schema.org/Invoice", "PaymentMethod")
                ->AddChoices($this->getGatwaysList())
                ->isNotTested();

        //====================================================================//
        // Payment Line Date
        $this->fieldsFactory()->Create(SPL_T_DATE)
                ->Identifier("_date_paid")
                ->InList("payments")
                ->Name(__("Date"))
                ->MicroData("http://schema.org/PaymentChargeSpecification", "validFrom")
//                ->Association("date@payments","mode@payments","amount@payments");
                ->Group($GroupName)
                ->isNotTested();

        //====================================================================//
        // Payment Line Payment Identifier
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("_transaction_id")
                ->InList("payments")
                ->Name(__("Transaction ID"))
                ->MicroData("http://schema.org/Invoice", "paymentMethodId")
//                ->Association("date@payments","mode@payments","amount@payments");
                ->Group($GroupName)
                ->isNotTested();

        //====================================================================//
        // Payment Line Amount
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_total_tax")
                ->InList("payments")
                ->Name(__("Total"))
                ->MicroData("http://schema.org/PaymentChargeSpecification", "price")
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
     *  @return       void
     */
    private function getPaymentsFields($Key, $FieldName)
    {
        // Check if List field & Init List Array
        $FieldId = self::lists()->InitOutput($this->Out, "payments", $FieldName);
        if (!$FieldId) {
            return;
        }
        
        //====================================================================//
        // Verify if Order Was Paid
        if ($this->Object->get_date_paid()) {
            //====================================================================//
            // Read Data from Order object
            $Data   =   $this->getPaymentData($FieldId);
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->Out, "payments", $FieldName, 0, $Data);
        }
        
        unset($this->In[$Key]);
    }
       
    /**
     * @abstract    Read Order Payment Field
     * @param       string  $FieldId
     * @return      mixed
     */
    private function getPaymentData($FieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldId) {
            case '_transaction_id':
                return  $this->Object->get_transaction_id();
            case '_total_tax':
                return  $this->Object->get_total();
                
            case '_date_paid':
                $Date   =   $this->Object->get_date_paid();
                if (is_a($Date, "WC_DateTime")) {
                    return  $Date->format(SPL_T_DATECAST);
                }
                return  $Date;
                
            case '_payment_method':
                return $this->encodePaymentMethod();
        }
        return null;
    }
    
    /**
     * @abstract    Read Available Payments Gatways List
     * @return      array
     */
    private function getGatwaysList()
    {
        $Result = array();
        
        foreach (wc()->payment_gateways()->get_available_payment_gateways() as $Gatway) {
            $Method =   $this->encodePaymentMethod($Gatway->id);
            $Result[ $Method ]  =   $Gatway->get_title();
        }

        return $Result;
    }
    
    /**
     *  @abstract     Try To Detect Payment method Standardized Name
     *
     *  @return     string
     */
    private function encodePaymentMethod($Method = null)
    {
        if (is_null($Method)) {
            $Method = $this->Object->get_payment_method();
        }
                
        //====================================================================//
        // Detect All Paypal Payment Methods
        if (strpos($Method, 'paypal') !== false) {
            return "PayPal";
        }
        
        //====================================================================//
        // Detect Payment Metyhod Type from Default Payment "known" methods
        switch ($Method) {
            case "bacs":
                return "ByBankTransferInAdvance";
            case "cheque":
                return "CheckInAdvance";
            case "paypal":
                return "PayPal";
            case "cod":
                return "COD";
                
            case "other":
            default:
                return "DirectDebit";
        }
        return $Method;
    }
    
    /**
     *  @abstract     Try To Detect Payment method Standardized Name
     *
     *  @return     string
     */
    private function decodePaymentMethod($Method)
    {
        
        //====================================================================//
        // Detect Payment Metyhod Type from Default Payment "known" methods
        switch ($Method) {
            case "ByBankTransferInAdvance":
                return "bacs";
            case "CheckInAdvance":
                return "cheque";
            case "PayPal":
                return "paypal";
            case "COD":
                return "cod";
        }
        return "other";
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
     *  @return       void
     */
    private function setPaymentsFields($FieldName, $Data)
    {
        // Check if List field
        if ($FieldName != "payments") {
            return;
        }
        // If Payments Array is Empty
        if (!count($Data)) {
            // Invalidate Payment
            $this->setGeneric("_date_paid", null);
            return;
        }
        // init Counters
        $Index          = 0;
        foreach ($Data as $PaymentData) {
            // Set Payments Core Data From First Item
            if ($Index) {
                continue;
            }
            $Index++;
            // Update Payment Method
            if ($this->encodePaymentMethod() != $PaymentData["_payment_method"]) {
                $this->setGeneric("_payment_method", $this->decodePaymentMethod($PaymentData["_payment_method"]));
            }
            // Update Transaction ID
            $this->setGeneric("_transaction_id", $PaymentData["_transaction_id"]);
            // Update Payment Date
            $CurrentDate   =   $this->Object->get_date_paid();
            if (is_a($CurrentDate, "WC_DateTime")) {
                $CurrentDate = $CurrentDate->format(SPL_T_DATECAST);
            }
            if ($CurrentDate !== $PaymentData["_date_paid"]) {
                $this->Object->set_date_paid($PaymentData["_date_paid"]);
                $this->needUpdate();
            }
        }
                
        unset($this->In["payments"]);
    }
}
