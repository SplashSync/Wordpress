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

namespace Splash\Local\Objects\CreditNote;

use Splash\Core\SplashCore as Splash;
use WC_Order_Refund;

/**
 * WooCommerce Credit Note Objects Lists
 */
trait ObjectListTrait
{
    /**
     * Build Objects List
     *
     * @param null|string $filter Filters for Object List.
     * @param array       $params Search parameters for result List.
     *
     * @return array
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        $data = array();
        //====================================================================//
        // Load Data From DataBase
        //
        // Refunds are fetched by type rather than by status: a refund inherits the
        // status of its parent order and filtering on it would hide legitimate
        // credit notes.
        $rawData = wc_get_orders(array(
            'type' => 'shop_order_refund',
            'status' => 'any',
            'limit' => (!empty($params["max"]) ? $params["max"] : 10),
            'offset' => (!empty($params["offset"]) ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"]) ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"]) ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter) ? $filter : ''),
        ));
        if (!is_array($rawData)) {
            $rawData = array();
        }
        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"] = $this->countCreditNotes();
        $data["meta"]["current"] = count($rawData);
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($rawData as $wcRefund) {
            if (!$wcRefund instanceof WC_Order_Refund) {
                continue;
            }
            $data[] = $this->toListCreditNote($wcRefund);
        }
        Splash::log()->deb(
            "MsgLocalTpl",
            __CLASS__,
            __FUNCTION__,
            " ".count($rawData)." Credit Notes Found."
        );

        return $data;
    }

    /**
     * Count Total Number of Credit Notes
     *
     * wc_orders_count() only knows about order statuses, so it cannot count
     * refunds. Ask WordPress for the post type count instead.
     *
     * @return int
     */
    private function countCreditNotes(): int
    {
        global $wpdb;

        return (int) $wpdb->get_var($wpdb->prepare(
            "SELECT COUNT(*) FROM {$wpdb->posts} WHERE post_type = %s",
            'shop_order_refund'
        ));
    }

    /**
     * Prepare a Credit Note for the Objects List
     *
     * @param WC_Order_Refund $wcRefund
     *
     * @return array
     */
    private function toListCreditNote(WC_Order_Refund $wcRefund): array
    {
        $date = $wcRefund->get_date_created();

        $parent = wc_get_order($wcRefund->get_parent_id());
        $parentTotal = $parent ? (float) $parent->get_total() : 0.0;

        return array(
            "id" => $wcRefund->get_id(),
            "reference" => $this->toListReference($wcRefund),
            "_datetime_created" => $date ? $date->format(SPL_T_DATETIMECAST) : null,
            "reason" => (string) $wcRefund->get_reason(),
            "is_order_fully_refunded" => ($parent && $parentTotal > 0.0)
                && (((float) $parent->get_total_refunded() + 0.01) >= $parentTotal),
            "total" => $wcRefund->get_total(),
        );
    }

    /**
     * Build a Credit Note Reference for the List
     *
     * Kept in step with CoreTrait::getCreditNoteReference().
     *
     * @param WC_Order_Refund $wcRefund
     *
     * @return string
     */
    private function toListReference(WC_Order_Refund $wcRefund): string
    {
        $ywpiNumber = (string) $wcRefund->get_meta('_ywpi_credit_note_formatted_number');
        if (!empty($ywpiNumber)) {
            return $ywpiNumber;
        }

        return sprintf("#%d-R%d", $wcRefund->get_parent_id(), $wcRefund->get_id());
    }
}
