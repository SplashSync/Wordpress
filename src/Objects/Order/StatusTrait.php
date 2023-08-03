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

use Splash\Local\Core\InvoiceStatusManager;
use Splash\Local\Core\OrderStatusManager;
use Splash\Local\Objects\Invoice as SplashInvoice;
use Splash\Models\Objects\Order\Status as OrderStatus;

/**
 * WooCommerce Order Statuses
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
        $isInvoice = ($this instanceof SplashInvoice);
        //====================================================================//
        // Order Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("status")
            ->name($isInvoice ? _("Order Status") : _("Status"))
            ->group(__("Status"))
            ->microData("http://schema.org/Order", "orderStatus")
            ->addChoices(OrderStatusManager::getOrderStatusChoices())
            ->isReadOnly($isInvoice)
            ->setPreferRead()
            ->isListed()
        ;
        if (!$isInvoice) {
            return;
        }
        //====================================================================//
        // Invoice Current Status
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("invoice_status")
            ->name(_("Status"))
            ->group(__("Status"))
            ->microData("http://schema.org/Invoice", "paymentStatus")
            ->isListed()
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
    private function getStatusFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'status':
                $orderStatus = $this->object->get_status();
                $this->out[$fieldName] = OrderStatusManager::encode($orderStatus) ?? $orderStatus;

                break;
            case 'invoice_status':
                $orderStatus = $this->object->get_status();
                $this->out[$fieldName] = InvoiceStatusManager::encode($orderStatus) ?? $orderStatus;

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
     * @param string      $fieldName Field Identifier / Name
     * @param null|string $fieldData Field Data
     *
     * @return void
     */
    private function setStatusFields(string $fieldName, ?string $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'status':
                //====================================================================//
                // Compare New vs Current
                $splashStatus = OrderStatusManager::encode($this->object->get_status());
                if (empty($fieldData) || empty($splashStatus) || ($splashStatus == $fieldData)) {
                    break;
                }
                //====================================================================//
                // Update Status
                $wcStatus = OrderStatusManager::decode($fieldData);
                if ($wcStatus) {
                    $this->object->set_status($wcStatus, "Updated by Splash!", true);
                    $this->needUpdate();
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
                return OrderStatus::DRAFT;
            case 'processing':
            case 'on-hold':
            case 'wc-awaiting-shipment':
            case 'awaiting-shipment':
                return OrderStatus::PROCESSING;
            case 'wc-shipped':
            case 'shipped':
                return OrderStatus::IN_TRANSIT;
            case 'completed':
                return OrderStatus::DELIVERED;
            case 'cancelled':
            case 'refunded':
            case 'failed':
                return OrderStatus::CANCELED;
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
            case OrderStatus::DRAFT:
            case OrderStatus::PAYMENT_DUE:
                return "pending";
            case OrderStatus::PROCESSING:
            case OrderStatus::PROCESSED:
            case OrderStatus::OUT_OF_STOCK:
                return "processing";
            case OrderStatus::IN_TRANSIT:
            case OrderStatus::TO_SHIP:
            case OrderStatus::PICKUP:
            case OrderStatus::PROBLEM:
                return "shipped";
            case OrderStatus::DELIVERED:
                return "completed";
            case OrderStatus::CANCELED:
                return "cancelled";
        }

        return null;
    }
}
