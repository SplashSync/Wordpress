<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Address;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    Wordpress Users ObjectList Functions
 */
trait ObjectListTrait
{
    
    //====================================================================//
    // Class Main Functions
    //====================================================================//
    
    /**
     * {@inheritdoc}
    */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        $data       = array();
        //====================================================================//
        // Load Dta From DataBase
        $RawData = get_users([
            'number'            =>      ( !empty($params["max"])        ? ($params["max"] / 2) : 10  ),
            'offset'            =>      ( !empty($params["offset"])     ? ($params["offset"] /2) : 0  ),
            'orderby'           =>      ( !empty($params["sortfield"])  ? $params["sortfield"] : 'id'  ),
            'order'             =>      ( !empty($params["sortorder"])  ? $params["sortorder"] : 'ASC' ),
            's'                 =>      ( !empty($filter)  ? $filter : '' ),
        ]);
        //====================================================================//
        // Store Meta Total & Current values
        $Totals = count_users();
        $data["meta"]["total"]      =   2 * $Totals['total_users'];
        $data["meta"]["current"]    =   2 * count($RawData);
        //====================================================================//
        // For each result, read information and add to $data
        foreach ($RawData as $User) {
            $data[] = array(
                "id"            =>  $this->encodeDeliveryId($User->ID),
                "roles"         =>  array_shift($User->roles),
                "first_name"    =>  get_user_meta($User->ID, $this->encodeFieldId("first_name", self::$Delivery), true),
                "last_name"     =>  get_user_meta($User->ID, $this->encodeFieldId("last_name", self::$Delivery), true),
                "postcode"      =>  get_user_meta($User->ID, $this->encodeFieldId("postcode", self::$Delivery), true),
                "city"          =>  get_user_meta($User->ID, $this->encodeFieldId("city", self::$Delivery), true),
                "phone"         =>  "N/A",
                "email"         =>  "N/A",
            );
            $data[] = array(
                "id"            =>  $this->encodeBillingId($User->ID),
                "first_name"    =>  get_user_meta($User->ID, $this->encodeFieldId("first_name", self::$Billing), true),
                "last_name"     =>  get_user_meta($User->ID, $this->encodeFieldId("last_name", self::$Billing), true),
                "postcode"      =>  get_user_meta($User->ID, $this->encodeFieldId("postcode", self::$Billing), true),
                "city"          =>  get_user_meta($User->ID, $this->encodeFieldId("city", self::$Billing), true),
                "phone"         =>  get_user_meta($User->ID, $this->encodeFieldId("phone", self::$Billing), true),
                "email"         =>  get_user_meta($User->ID, $this->encodeFieldId("email", self::$Billing), true),
            );
        }
        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " " . count($RawData) . " Users Found.");
        return $data;
    }
}
