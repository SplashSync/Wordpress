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
    protected function buildStatusFields(): void
    {
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("status")
            ->name(_("Status"))
            ->isListed()
            ->group(__("Status"))
            ->microData("http://schema.org/Order", "orderStatus")
            ->addChoice("OrderCanceled", __("Cancelled"))
            ->addChoice("OrderDraft", __("Pending payment"))
            ->addChoice("OrderProcessing", __("Processing"))
            ->addChoice("OrderDelivered", __("Completed"))
        ;

        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            //====================================================================//
            // Force Order Current Status as ReadOnly
            $this->fieldsFactory()->name(_("Order Status"))->isReadOnly();
            //====================================================================//
            // Invoice Current Status
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier("invoice_status")
                ->name(_("Status"))
                ->group(__("Status"))
                ->microData("http://schema.org/Invoice", "paymentStatus")
            ;
        }

        //====================================================================//
        // Is Draft
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isdraft")
            ->group(__("Status"))
            ->name(__("Order")." : ".__("Pending payment"))
            ->microData("http://schema.org/OrderStatus", "OrderDraft")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("iscanceled")
            ->group(__("Status"))
            ->name(__("Order")." : ".__("Cancelled"))
            ->microData("http://schema.org/OrderStatus", "OrderCancelled")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isvalidated")
            ->group(__("Status"))
            ->name(__("Order")." : ".__("Processing"))
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isclosed")
            ->name(__("Order")." : ".__("Completed"))
            ->group(__("Status"))
            ->microData("http://schema.org/OrderStatus", "OrderDelivered")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("ispaid")
            ->name(__("Order")." : ".__("Paid"))
            ->group(__("Status"))
            ->isReadOnly()
        ;
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->microData("http://schema.org/PaymentStatusType", "PaymentComplete");
        } else {
            $this->fieldsFactory()
                ->microData("http://schema.org/OrderStatus", "OrderPaid");
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
    private function getStatusFields(string $key, string $fieldName): void
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
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setStatusFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'status':
                if ($this->encodeStatus() != $fieldData) {
                    /** @var string $fieldData */
                    $this->object->set_status((string) $this->decodeStatus($fieldData), "Updated by Splash!", true);
                }

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Order Status Conversion
    //====================================================================//

    /**
     * Encode WC Order Status to Splash Standard Status
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function encodeStatus(): string
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
    private function decodeStatus(string $status): ?string
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
    // Invoice Status Conversion
    //====================================================================//

    /**
     * Encode WC Order Status to Splash Standard Status
     *
     * @return string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function encodeInvoiceStatus(): string
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
