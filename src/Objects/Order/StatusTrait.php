<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Order Status Data Access
 */
trait StatusTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildStatusFields()
    {
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("status")
            ->Name(_("Status"))
            ->isListed()
            ->Group(__("Status"))
            ->MicroData("http://schema.org/Order", "orderStatus")
            ->AddChoice("OrderCanceled", __("Cancelled"))
            ->AddChoice("OrderDraft", __("Pending payment"))
            ->AddChoice("OrderProcessing", __("Processing"))
            ->AddChoice("OrderDelivered", __("Completed"))
        ;

        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            //====================================================================//
            // Force Order Current Status as ReadOnly
            $this->fieldsFactory()->Name(_("Order Status"))->isReadOnly();
            //====================================================================//
            // Invoice Current Status
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("invoice_status")
                ->Name(_("Status"))
                ->Group(__("Status"))
                ->MicroData("http://schema.org/Invoice", "paymentStatus")
            ;
        }

        //====================================================================//
        // Is Draft
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("isdraft")
            ->Group(__("Status"))
            ->Name(__("Order")." : ".__("Pending payment"))
            ->MicroData("http://schema.org/OrderStatus", "OrderDraft")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("iscanceled")
            ->Group(__("Status"))
            ->Name(__("Order")." : ".__("Cancelled"))
            ->MicroData("http://schema.org/OrderStatus", "OrderCancelled")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("isvalidated")
            ->Group(__("Status"))
            ->Name(__("Order")." : ".__("Processing"))
            ->MicroData("http://schema.org/OrderStatus", "OrderProcessing")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("isclosed")
            ->Name(__("Order")." : ".__("Completed"))
            ->Group(__("Status"))
            ->MicroData("http://schema.org/OrderStatus", "OrderDelivered")
            ->Association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly();

        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->Create(SPL_T_BOOL)
            ->Identifier("ispaid")
            ->Name(__("Order")." : ".__("Paid"))
            ->Group(__("Status"))
            ->isReadOnly();
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/PaymentStatusType", "PaymentComplete");
        } else {
            $this->fieldsFactory()
                ->MicroData("http://schema.org/OrderStatus", "OrderPaid");
        }
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
    private function getStatusFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'status':
                $this->out[$fieldName] = $this->encodeStatus();

                break;
            case 'invoice_status':
                $this->out[$fieldName] = $this->encodeInvoiceStatus();

                break;
            case 'isdraft':
                $this->out[$fieldName] = in_array($this->object->get_status(), array("pending"), true);

                break;
            case 'iscanceled':
                $this->out[$fieldName] = in_array(
                    $this->object->get_status(),
                    array("canceled", "refunded", "failed"),
                    true
                );

                break;
            case 'isvalidated':
                $this->out[$fieldName] = in_array(
                    $this->object->get_status(),
                    array(
                        "processing",
                        "on-hold",
                        "wc-awaiting-shipment",
                        "wc-shipped",
                        "awaiting-shipment",
                        "shipped"
                    ),
                    true
                );

                break;
            case 'isclosed':
                $this->out[$fieldName] = in_array($this->object->get_status(), array("completed"), true);

                break;
            case 'ispaid':
                $this->out[$fieldName] = in_array(
                    $this->object->get_status(),
                    array(
                        "processing", "on-hold", "completed", "wc-awaiting-shipment",
                        "wc-shipped", "awaiting-shipment", "shipped"
                    ),
                    true
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
    private function setStatusFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'status':
                if ($this->encodeStatus() != $fieldData) {
                    $this->object->set_status($this->decodeStatus($fieldData), "Updated by Splash!", true);
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Order Status Convertion
    //====================================================================//

    /**
     * Encode WC Order Status to Splash Standard Status
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function encodeStatus()
    {
        switch ($this->object->get_status()) {
            case 'pending':
                return "OrderDraft";
            case 'processing':
            case 'on-hold':
            case 'wc-awaiting-shipment':
            case 'wc-shipped':
            case 'awaiting-shipment':
            case 'shipped':
                return "OrderProcessing";
            case 'completed':
                return "OrderDelivered";
            case 'cancelled':
            case 'refunded':
            case 'failed':
                return "OrderCanceled";
        }

        return "Unknown (".$this->object->get_status().")";
    }

    /**
     * Decode Splash Standard Status to WC Order Status
     *
     * @param string $status Splash Standard Status
     *
     * @return null|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function decodeStatus($status)
    {
        switch ($status) {
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

        return null;
    }

    //====================================================================//
    // Invoice Status Convertion
    //====================================================================//

    /**
     * Encode WC Order Status to Splash Standard Status
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function encodeInvoiceStatus()
    {
        switch ($this->object->get_status()) {
            case 'pending':
                return "PaymentDraft";
            case 'on-hold':
                return "PaymentDue";
            case 'processing':
            case 'wc-awaiting-shipment':
            case 'wc-shipped':
            case 'awaiting-shipment':
            case 'shipped':
            case 'completed':
                return "PaymentComplete";
            case 'cancelled':
            case 'refunded':
            case 'failed':
                return "PaymentCanceled";
        }

        return "Unknown (".$this->object->get_status().")";
    }
}
