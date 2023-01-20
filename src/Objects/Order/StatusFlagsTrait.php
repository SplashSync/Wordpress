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

use Splash\Local\Objects\Invoice as SplashInvoice;

/**
 * WooCommerce Order Status Flags
 */
trait StatusFlagsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildStatusFlagsFields(): void
    {
        //====================================================================//
        // Is Draft
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isdraft")
            ->group(__("Status"))
            ->name(__("Pending payment"))
            ->microData("http://schema.org/OrderStatus", "OrderDraft")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Canceled
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("iscanceled")
            ->group(__("Status"))
            ->name(__("Cancelled"))
            ->microData("http://schema.org/OrderStatus", "OrderCancelled")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Validated
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isvalidated")
            ->group(__("Status"))
            ->name(__("Validated"))
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Processing
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isProcessing")
            ->group(__("Status"))
            ->name(__("Processing"))
            ->microData("http://schema.org/OrderStatus", "OrderProcessing")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Closed
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("isclosed")
            ->name(__("Completed"))
            ->group(__("Status"))
            ->microData("http://schema.org/OrderStatus", "OrderDelivered")
            ->association("isdraft", "iscanceled", "isvalidated", "isclosed")
            ->isReadOnly()
        ;
        //====================================================================//
        // Is Paid
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("ispaid")
            ->name(__("Paid"))
            ->group(__("Status"))
            ->isReadOnly()
        ;
        if ($this instanceof SplashInvoice) {
            $this->fieldsFactory()->microData(
                "http://schema.org/PaymentStatusType",
                "PaymentComplete"
            );
        } else {
            $this->fieldsFactory()->microData(
                "http://schema.org/OrderStatus",
                "OrderPaid"
            );
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
    private function getStatusFlagsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
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
            case 'isProcessing':
                $this->out[$fieldName] = ("processing" == $this->object->get_status());

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
}
