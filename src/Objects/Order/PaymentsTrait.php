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

namespace Splash\Local\Objects\Order;

/**
 * WooCommerce Order Payment Data Access
 */
trait PaymentsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildPaymentsFields()
    {
        $groupName = __("Payments");

        //====================================================================//
        // Payment Line Payment Method
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier('_payment_method')
            ->InList("payments")
            ->Group($groupName)
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
            ->Group($groupName)
            ->isNotTested();

        //====================================================================//
        // Payment Line Payment Identifier
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("_transaction_id")
            ->InList("payments")
            ->Name(__("Transaction ID"))
            ->MicroData("http://schema.org/Invoice", "paymentMethodId")
            ->Group($groupName)
            ->isNotTested();

        //====================================================================//
        // Payment Line Amount
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("_total_tax")
            ->InList("payments")
            ->Name(__("Total"))
            ->MicroData("http://schema.org/PaymentChargeSpecification", "price")
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
    private function getPaymentsFields($key, $fieldName)
    {
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "payments", $fieldName);
        if (!$fieldId) {
            return;
        }

        //====================================================================//
        // Verify if Order Was Paid
        if ($this->object->get_date_paid()) {
            //====================================================================//
            // Read Data from Order object
            $data = $this->getPaymentData($fieldId);
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "payments", $fieldName, 0, $data);
        }

        unset($this->in[$key]);
    }

    /**
     * Read Order Payment Field
     *
     * @param string $fieldId
     *
     * @return mixed
     */
    private function getPaymentData($fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            case '_transaction_id':
                return  $this->object->get_transaction_id();
            case '_total_tax':
                return  $this->object->get_total();
            case '_date_paid':
                $date = $this->object->get_date_paid();
                if (is_a($date, "WC_DateTime")) {
                    return  $date->format(SPL_T_DATECAST);
                }

                return  $date;
            case '_payment_method':
                return $this->encodePaymentMethod();
        }

        return null;
    }

    /**
     * Read Available Payments Gatways List
     *
     * @return array
     */
    private function getGatwaysList()
    {
        $result = array();

        foreach (wc()->payment_gateways()->get_available_payment_gateways() as $gatway) {
            $method = $this->encodePaymentMethod($gatway->id);
            $result[ $method ] = $gatway->get_title();
        }

        return $result;
    }

    /**
     * Try To Detect Payment method Standardized Name
     *
     * @param null|mixed $method
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function encodePaymentMethod($method = null)
    {
        if (is_null($method)) {
            $method = $this->object->get_payment_method();
        }

        //====================================================================//
        // Detect All Paypal Payment Methods
        if (false !== strpos($method, 'paypal')) {
            return "PayPal";
        }

        //====================================================================//
        // Detect Payment Metyhod Type from Default Payment "known" methods
        switch (strtolower($method)) {
            case "bacs":
            case "amazon":
                return "ByBankTransferInAdvance";
            case "cheque":
                return "CheckInAdvance";
            case "paypal":
                return "PayPal";
            case "cod":
                return "COD";
            case "cash":
                return "Cash";
            case "other":
            default:
                return "DirectDebit";
        }
    }

    /**
     * Try To Detect Payment method Standardized Name
     *
     * @param mixed $method
     *
     * @return string
     */
    private function decodePaymentMethod($method)
    {
        //====================================================================//
        // Detect Payment Metyhod Type from Default Payment "known" methods
        switch ($method) {
            case "ByBankTransferInAdvance":
                return "bacs";
            case "CheckInAdvance":
                return "cheque";
            case "PayPal":
                return "paypal";
            case "COD":
                return "cod";
            case "Cash":
                return "cash";
        }

        return "other";
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
    private function setPaymentsFields($fieldName, $fieldData)
    {
        // Check if List field
        if ("payments" != $fieldName) {
            return;
        }
        // If Payments Array is Empty
        if (!count($fieldData)) {
            // Invalidate Payment
            $this->setGeneric("_date_paid", null);

            return;
        }
        // init Counters
        $index = 0;
        foreach ($fieldData as $paymentData) {
            // Set Payments Core Data From First Item
            if ($index) {
                continue;
            }
            $index++;
            // Update Payment Method
            if ($this->encodePaymentMethod() != $paymentData["_payment_method"]) {
                $this->setGeneric("_payment_method", $this->decodePaymentMethod($paymentData["_payment_method"]));
            }
            // Update Transaction ID
            $this->setGeneric("_transaction_id", $paymentData["_transaction_id"]);
            // Update Payment Date
            $currentDate = $this->object->get_date_paid();
            if (is_a($currentDate, "WC_DateTime")) {
                $currentDate = $currentDate->format(SPL_T_DATECAST);
            }
            if ($currentDate !== $paymentData["_date_paid"]) {
                $this->object->set_date_paid($paymentData["_date_paid"]);
                $this->needUpdate();
            }
        }

        unset($this->in["payments"]);
    }
}
