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

use Splash\Core\SplashCore      as Splash;

/**
 * WordPress Users ObjectList Functions
 */
trait ObjectListTrait
{
    //====================================================================//
    // Class Main Functions
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        $data = array();
        //====================================================================//
        // Load Dta From DataBase
        $rawData = get_users(array(
            'number' => (!empty($params["max"])        ? ($params["max"] / 2) : 10),
            'offset' => (!empty($params["offset"])     ? ($params["offset"] / 2) : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));
        //====================================================================//
        // Store Meta Total & Current values
        $totals = count_users();
        $data["meta"]["total"] = 2 * $totals['total_users'];
        $data["meta"]["current"] = 2 * count($rawData);
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($rawData as $user) {
            $data[] = array(
                "id" => $this->encodeDeliveryId($user->ID),
                "roles" => array_shift($user->roles),
                "first_name" => get_user_meta($user->ID, $this->encodeFieldId("first_name", self::$delivery), true),
                "last_name" => get_user_meta($user->ID, $this->encodeFieldId("last_name", self::$delivery), true),
                "postcode" => get_user_meta($user->ID, $this->encodeFieldId("postcode", self::$delivery), true),
                "city" => get_user_meta($user->ID, $this->encodeFieldId("city", self::$delivery), true),
                "phone" => "N/A",
                "email" => "N/A",
            );
            $data[] = array(
                "id" => $this->encodeBillingId($user->ID),
                "first_name" => get_user_meta($user->ID, $this->encodeFieldId("first_name", self::$billing), true),
                "last_name" => get_user_meta($user->ID, $this->encodeFieldId("last_name", self::$billing), true),
                "postcode" => get_user_meta($user->ID, $this->encodeFieldId("postcode", self::$billing), true),
                "city" => get_user_meta($user->ID, $this->encodeFieldId("city", self::$billing), true),
                "phone" => get_user_meta($user->ID, $this->encodeFieldId("phone", self::$billing), true),
                "email" => get_user_meta($user->ID, $this->encodeFieldId("email", self::$billing), true),
            );
        }
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Users Found.");

        return $data;
    }
}
