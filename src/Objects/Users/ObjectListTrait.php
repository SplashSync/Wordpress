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

use Splash\Core\SplashCore as Splash;

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
        // Load Data From DataBase
        $queryArgs = $this->toListQueryArgs($filter, $params);
        $rawData = get_users($queryArgs);
        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"] = $this->countUsers($queryArgs);
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

    /**
     * Build Users List Query Args
     *
     * Filter: Users are Searched by Email, Username or Display Name
     * (standard wp_users columns only, no meta scan).
     */
    private function toListQueryArgs(?string $filter, array $params): array
    {
        $queryArgs = array(
            'number' => (!empty($params["max"]) ? $params["max"] : 10),
            'offset' => (!empty($params["offset"]) ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"]) ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"]) ? $params["sortorder"] : 'ASC'),
        );
        if (!empty($filter)) {
            $queryArgs['search'] = '*'.$filter.'*';
            $queryArgs['search_columns'] = array('user_email', 'user_login', 'display_name');
        }

        return $queryArgs;
    }

    /**
     * Count Users for Listing, Filter Included
     */
    private function countUsers(array $queryArgs): int
    {
        //====================================================================//
        // No Filter: Fast Global Count
        if (empty($queryArgs['search'])) {
            $totals = count_users();

            return (int) $totals['total_users'];
        }
        //====================================================================//
        // Filtered: Count Matched Users IDs
        unset($queryArgs['number'], $queryArgs['offset']);
        $queryArgs['fields'] = 'ID';

        return count(get_users($queryArgs));
    }
}
