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

use Splash\Local\Core\OrderStatusManager;
use Splash\Local\Objects\Invoice as SplashInvoice;
use Splash\Models\Objects\Order\Status;

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
            ->microData("http://schema.org/OrderStatus", "OrderValidated")
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
    protected function getStatusFlagsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'isdraft':
                $this->out[$fieldName] = Status::isDraft($this->getSplashOrderStatus());

                break;
            case 'iscanceled':
                $this->out[$fieldName] = Status::isCanceled($this->getSplashOrderStatus());

                break;
            case 'isvalidated':
                $this->out[$fieldName] = Status::isValidated($this->getSplashOrderStatus());

                break;
            case 'isProcessing':
                $this->out[$fieldName] = Status::isProcessing($this->getSplashOrderStatus());

                break;
            case 'isclosed':
                $this->out[$fieldName] = Status::isDelivered($this->getSplashOrderStatus());

                break;
            case 'ispaid':
                //====================================================================//
                // An invoice is settled when money was received for it, and a
                // refund does not unsettle it: the reversal is a credit note,
                // a separate document, and the original invoice stays paid.
                //
                // Neither WooCommerce method answers that on its own.
                // is_paid() reads the CURRENT status, so it reports a refunded
                // order as unpaid — true of the order, false of the invoice.
                // get_date_paid() reads the past, but WooCommerce stamps it on
                // every status transition into a paid status, even when no
                // money moved: zero-value orders, and orders switched to
                // processing and cancelled the same day, all carry one.
                //
                // So ask both, and let the status arbitrate. A cancelled or
                // failed order never produced a receivable to settle; anything
                // else that was once paid still has an invoice to match.
                $this->out[$fieldName] = $this->object->is_paid()
                    || (
                        (bool) $this->object->get_date_paid()
                        && !in_array(
                            $this->object->get_status(),
                            array('cancelled', 'failed', 'checkout-draft', 'trash'),
                            true
                        )
                    );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Get Order Splash Status
     *
     * @return string
     */
    private function getSplashOrderStatus(): string
    {
        $orderStatus = $this->object->get_status();

        return (string) (OrderStatusManager::encode($orderStatus) ?? $orderStatus);
    }
}
