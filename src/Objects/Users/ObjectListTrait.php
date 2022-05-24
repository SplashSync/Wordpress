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

namespace Splash\Local\Objects\Users;

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
            'number' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));
        //====================================================================//
        // Store Meta Total & Current values
        $totals = count_users();
        $data["meta"]["total"] = $totals['total_users'];
        $data["meta"]["current"] = count($rawData);
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($rawData as $user) {
            $data[] = array(
                "id" => $user->ID,
                "user_login" => $user->user_login,
                "user_email" => $user->user_email,
                "roles" => array_shift($user->roles),
                "first_name" => get_user_meta($user->ID, "first_name", true),
                "last_name" => get_user_meta($user->ID, "last_name", true),
            );
        }
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Users Found.");

        return $data;
    }
}
