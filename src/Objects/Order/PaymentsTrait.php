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
    protected function buildPaymentsFields(): void
    {
        $groupName = __("Payments");

        //====================================================================//
        // Payment Line Payment Method
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier('_payment_method')
            ->inList("payments")
            ->group($groupName)
            ->name(__("Method"))
            ->microData("http://schema.org/Invoice", "PaymentMethod")
            ->addChoices($this->getGatewaysList())
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->identifier("_date_paid")
            ->inList("payments")
            ->name(__("Date"))
            ->microData("http://schema.org/PaymentChargeSpecification", "validFrom")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Payment Identifier
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("_transaction_id")
            ->inList("payments")
            ->name(__("Transaction ID"))
            ->microData("http://schema.org/Invoice", "paymentMethodId")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Payment Line Amount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("_total_tax")
            ->inList("payments")
            ->name(__("Total"))
            ->microData("http://schema.org/PaymentChargeSpecification", "price")
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
    protected function getPaymentsFields(string $key, string $fieldName): void
    {
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "payments", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Verify if Order Was Paid
        if ($this->object->get_date_paid() && ("refunded" != $this->object->get_status())) {
            //====================================================================//
            // Read Data from Order object
            $data = $this->getPaymentData($fieldId);
            //====================================================================//
            // Insert Data in List
            self::lists()->insert($this->out, "payments", $fieldName, 0, $data);
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string     $fieldName Field Identifier / Name
     * @param null|array $fieldData Field Data
     *
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    protected function setPaymentsFields(string $fieldName, ?array $fieldData): void
    {
        //====================================================================//
        // Check if List field
        if ("payments" != $fieldName) {
            return;
        }
        //====================================================================//
        // If Payments Array is Empty
        if (empty($fieldData)) {
            // Invalidate Payment
            $this->setGeneric("_date_paid", null);
            unset($this->in["payments"]);

            return;
        }
        //====================================================================//
        // init Counters
        $index = 0;
        foreach ($fieldData as $paymentData) {
            //====================================================================//
            // Set Payments Core Data From First Item
            if ($index) {
                continue;
            }
            $index++;
            //====================================================================//
            // Update Payment Method
            if ($this->encodePaymentMethod() != $paymentData["_payment_method"]) {
                $this->setGeneric(
                    "_payment_method",
                    $this->decodePaymentMethod($paymentData["_payment_method"])
                );
            }
            //====================================================================//
            // Update Transaction ID
            $this->setGeneric("_transaction_id", $paymentData["_transaction_id"]);
            //====================================================================//
            // Update Payment Date
            $currentDate = $this->object->get_date_paid();
            if ($currentDate && is_a($currentDate, "WC_DateTime")) {
                $currentDate = $currentDate->format(SPL_T_DATECAST);
            }
            if ($currentDate !== $paymentData["_date_paid"]) {
                $this->object->set_date_paid($paymentData["_date_paid"]);
                $this->needUpdate();
            }
        }

        unset($this->in["payments"]);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Read Order Payment Field
     *
     * @param string $fieldId
     *
     * @return mixed
     */
    private function getPaymentData(string $fieldId)
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
                if ($date && is_a($date, "WC_DateTime")) {
                    return  $date->format(SPL_T_DATECAST);
                }

                return  $date;
            case '_payment_method':
                return $this->encodePaymentMethod();
        }

        return null;
    }

    /**
     * Read Available Payments Gateways List
     *
     * @return array
     */
    private function getGatewaysList(): array
    {
        $result = array();

        foreach (wc()->payment_gateways()->get_available_payment_gateways() as $gateway) {
            $method = $this->encodePaymentMethod($gateway->id);
            $result[ $method ] = $gateway->get_title();
        }

        return $result;
    }

    /**
     * Try To Detect Payment method Standardized Name
     *
     * @param null|string $method WooCommerce Gateway Id, Current Order Gateway if Null
     *
     * @return string
     */
    private function encodePaymentMethod(string $method = null): string
    {
        if (is_null($method)) {
            /** @var string $method */
            $method = $this->object->get_payment_method();
        }

        //====================================================================//
        // Filter the Splash code detected for a WooCommerce Gateway
        //
        // Gateways this connector does not know about fall back to
        // "DirectDebit", so a bank transfer, a voucher and a card all reach the
        // target as the same thing. Use this filter to give a custom gateway a
        // code of its own — targets resolve unknown codes against their own
        // payment methods.
        //
        // @param string $code   Splash payment method code
        // @param string $method WooCommerce gateway id
        return (string) apply_filters(
            'splash_encode_payment_method',
            $this->detectPaymentMethod($method),
            $method
        );
    }

    /**
     * Detect Payment method Standardized Name from Known Gateways
     *
     * @param string $method WooCommerce Gateway Id
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function detectPaymentMethod(string $method): string
    {
        //====================================================================//
        // Detect All Paypal Payment Methods
        if (false !== strpos($method, 'paypal')) {
            return "PayPal";
        }
        // WooCommerce PayPal Payments
        if (false !== strpos($method, 'ppcp-')) {
            return "PayPal";
        }

        //====================================================================//
        // Detect Payment Method Type from Default Payment "known" methods
        switch (strtolower($method)) {
            case "bacs":
            case "amazon":
                return "ByBankTransferInAdvance";
            case "cheque":
                return "CheckInAdvance";
            case "paypal":
                return "PayPal";
            case "cod":
            case "alma":
                return "COD";
            case "cash":
                return "Cash";
            case "other":
            default:
                return "DirectDebit";
        }
    }

    /**
     * Try To Detect WooCommerce Gateway Id from Standardized Name
     *
     * @param string $method Splash Payment Method Code
     *
     * @return string
     */
    private function decodePaymentMethod(string $method): string
    {
        //====================================================================//
        // Filter the WooCommerce Gateway used for a Splash code
        //
        // Kept symmetrical with splash_encode_payment_method: a custom code
        // mapped on the way out must map back on the way in, otherwise writing
        // an order would replace its real gateway with "other".
        //
        // @param string $gateway WooCommerce gateway id
        // @param string $method  Splash payment method code
        return (string) apply_filters(
            'splash_decode_payment_method',
            $this->detectGateway($method),
            $method
        );
    }

    /**
     * Detect WooCommerce Gateway Id from Known Standardized Names
     *
     * @param string $method Splash Payment Method Code
     *
     * @return string
     */
    private function detectGateway(string $method): string
    {
        //====================================================================//
        // Detect Payment Method Type from Default Payment "known" methods
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
}
