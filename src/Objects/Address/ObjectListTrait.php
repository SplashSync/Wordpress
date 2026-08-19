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

namespace Splash\Local\Objects\Address;

use Splash\Core\SplashCore as Splash;
use Splash\Local\Core\AddressesManager;
use Splash\Local\Dictionary\AddressTypes;
use WC_Order;
use WP_User;

/**
 * WordPress Users & Orders Addresses ObjectList Functions
 *
 * The list is built from two virtual segments:
 *  - Customers Addresses (WP Users meta, one row per active user type)
 *  - Orders Addresses (WC Orders, one row per active order type, read-only)
 */
trait ObjectListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $max = !empty($params["max"]) ? (int) $params["max"] : 25;
        $offset = !empty($params["offset"]) ? (int) $params["offset"] : 0;
        //====================================================================//
        // Resolve Filter: Addresses are Searched by WP User Email
        $filterUserIds = $this->resolveFilterUserIds($filter);
        //====================================================================//
        // Detect Active Addresses Types
        $userTypes = AddressesManager::getActiveTypes(AddressTypes::USERS);
        $orderTypes = AddressesManager::getActiveTypes(AddressTypes::ORDERS);
        //====================================================================//
        // Count Totals for Each Segment
        $usersTotal = count($userTypes) * $this->countUsers($filterUserIds);
        $ordersTotal = count($orderTypes) * $this->countOrders($orderTypes, $filterUserIds);
        //====================================================================//
        // Store Meta Totals
        $data = array();
        $data["meta"]["total"] = $usersTotal + $ordersTotal;
        //====================================================================//
        // Walk on Users Addresses Segment
        $rows = $this->getUsersAddressesRows($userTypes, $offset, $max, $filterUserIds);
        //====================================================================//
        // Walk on Orders Addresses Segment
        $ordersOffset = ($offset > $usersTotal) ? ($offset - $usersTotal) : 0;
        if (count($rows) < $max) {
            $rows = array_merge(
                $rows,
                $this->getOrdersAddressesRows($orderTypes, $ordersOffset, $max - count($rows), $filterUserIds)
            );
        }
        $data["meta"]["current"] = count($rows);
        //====================================================================//
        // Push Rows to Result
        foreach ($rows as $row) {
            $data[] = $row;
        }
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rows)." Addresses Found.");

        return $data;
    }

    /**
     * Build Users Addresses Rows for Requested Window
     *
     * @param string[]   $userTypes     Active User Address Types
     * @param null|int[] $filterUserIds Filtered WP Users IDs, Null if no Filter
     *
     * @return array[]
     */
    private function getUsersAddressesRows(array $userTypes, int $offset, int $max, ?array $filterUserIds): array
    {
        $nbTypes = count($userTypes);
        if (!$nbTypes) {
            return array();
        }
        //====================================================================//
        // Load Users for Requested Window
        $args = array(
            'number' => (int) ceil($max / $nbTypes) + 1,
            'offset' => (int) floor($offset / $nbTypes),
            'orderby' => 'ID',
            'order' => 'ASC',
        );
        if (null !== $filterUserIds) {
            $args['include'] = $filterUserIds ?: array(0);
        }
        /** @var WP_User[] $wpUsers */
        $wpUsers = get_users($args);
        //====================================================================//
        // Build All Rows for Loaded Users
        $rows = array();
        foreach ($wpUsers as $user) {
            foreach ($userTypes as $addressType) {
                $rows[] = $this->toUserAddressRow($user, $addressType);
            }
        }

        //====================================================================//
        // Extract Requested Window
        return array_slice($rows, $offset % $nbTypes, $max);
    }

    /**
     * Build Orders Addresses Rows for Requested Window
     *
     * @param string[]   $orderTypes    Active Order Address Types
     * @param null|int[] $filterUserIds Filtered WP Users IDs, Null if no Filter
     *
     * @return array[]
     */
    private function getOrdersAddressesRows(array $orderTypes, int $offset, int $max, ?array $filterUserIds): array
    {
        $nbTypes = count($orderTypes);
        if (!$nbTypes || ($max <= 0)) {
            return array();
        }
        //====================================================================//
        // Load Orders for Requested Window
        $args = array(
            'type' => 'shop_order',
            'limit' => (int) ceil($max / $nbTypes) + 1,
            'offset' => (int) floor($offset / $nbTypes),
            'orderby' => 'ID',
            'order' => 'ASC',
        );
        if (null !== $filterUserIds) {
            $args['customer_id'] = $filterUserIds ?: array(0);
        }
        $wcOrders = wc_get_orders($args);
        if (!is_array($wcOrders)) {
            return array();
        }
        //====================================================================//
        // Build All Rows for Loaded Orders
        $rows = array();
        /** @var WC_Order $wcOrder */
        foreach ($wcOrders as $wcOrder) {
            foreach ($orderTypes as $addressType) {
                $rows[] = $this->toOrderAddressRow($wcOrder, $addressType);
            }
        }

        //====================================================================//
        // Extract Requested Window
        return array_slice($rows, $offset % $nbTypes, $max);
    }

    /**
     * Convert Wp User to Address Row
     */
    private function toUserAddressRow(WP_User $user, string $addressType): array
    {
        return array(
            "id" => AddressTypes::encode($addressType, (string) $user->ID),
            "first_name" => $this->getUserAddressMeta($user->ID, "first_name", $addressType),
            "last_name" => $this->getUserAddressMeta($user->ID, "last_name", $addressType),
            "postcode" => $this->getUserAddressMeta($user->ID, "postcode", $addressType),
            "city" => $this->getUserAddressMeta($user->ID, "city", $addressType),
            "phone" => $this->getUserAddressMeta($user->ID, "phone", $addressType),
            "email" => (AddressTypes::BILLING === $addressType)
                ? $this->getUserAddressMeta($user->ID, "email", AddressTypes::BILLING)
                : $user->user_email,
        );
    }

    /**
     * Convert Wc Order to Address Row
     */
    private function toOrderAddressRow(WC_Order $wcOrder, string $addressType): array
    {
        $side = (AddressTypes::INVOICING === $addressType) ? "billing" : "shipping";
        $address = $wcOrder->get_address($side);

        return array(
            "id" => AddressTypes::encode($addressType, (string) $wcOrder->get_id()),
            "first_name" => $address["first_name"] ?? "",
            "last_name" => $address["last_name"] ?? "",
            "postcode" => $address["postcode"] ?? "",
            "city" => $address["city"] ?? "",
            "phone" => $address["phone"] ?? "",
            "email" => $address["email"] ?? $wcOrder->get_billing_email(),
        );
    }

    /**
     * Count Users Available for Addresses Listing
     *
     * @param null|int[] $filterUserIds Filtered WP Users IDs, Null if no Filter
     */
    private function countUsers(?array $filterUserIds): int
    {
        if (null !== $filterUserIds) {
            return count($filterUserIds);
        }
        $totals = count_users();

        return (int) $totals['total_users'];
    }

    /**
     * Count Orders Available for Addresses Listing
     *
     * @param string[]   $orderTypes    Active Order Address Types
     * @param null|int[] $filterUserIds Filtered WP Users IDs, Null if no Filter
     */
    private function countOrders(array $orderTypes, ?array $filterUserIds): int
    {
        if (!count($orderTypes)) {
            return 0;
        }
        $args = array(
            'type' => 'shop_order',
            'limit' => 1,
            'paginate' => true,
        );
        if (null !== $filterUserIds) {
            $args['customer_id'] = $filterUserIds ?: array(0);
        }
        $result = wc_get_orders($args);

        return is_object($result) ? (int) $result->total : 0;
    }

    /**
     * Resolve List Filter to WP Users IDs
     *
     * Addresses are searched by User Email, Username or Addresses Phones.
     *
     * @return null|int[] Matched Users IDs, Null if no Filter
     */
    private function resolveFilterUserIds(?string $filter): ?array
    {
        if (empty($filter)) {
            return null;
        }
        //====================================================================//
        // Search Users by Email or Username
        /** @var int[] $userIds */
        $userIds = get_users(array(
            'fields' => 'ID',
            'search' => '*'.$filter.'*',
            'search_columns' => array('user_email', 'user_login'),
        ));
        //====================================================================//
        // Search Users by Addresses Phones: LIKE on usermeta cannot use
        // indexes, so only run this query for phone-like filters
        $metaUserIds = array();
        if (preg_match('/^[0-9 +().-]{6,}$/', $filter)) {
            /** @var int[] $metaUserIds */
            $metaUserIds = get_users(array(
                'fields' => 'ID',
                'number' => 100,
                'meta_query' => array(
                    'relation' => 'OR',
                    array('key' => 'billing_phone', 'value' => $filter, 'compare' => 'LIKE'),
                    array('key' => 'shipping_phone', 'value' => $filter, 'compare' => 'LIKE'),
                ),
            ));
        }

        return array_values(array_unique(array_map('intval', array_merge($userIds, $metaUserIds))));
    }

    /**
     * Read User Address Meta Value
     *
     * @return mixed
     */
    private function getUserAddressMeta(int $userId, string $fieldId, string $addressType)
    {
        return get_user_meta($userId, $this->encodeFieldId($fieldId, $addressType), true);
    }
}
