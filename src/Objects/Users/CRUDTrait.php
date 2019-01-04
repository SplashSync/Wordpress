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

namespace Splash\Local\Objects\Users;

use ArrayObject;
use WP_User;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    Wordpress Users CRUD Functions
 */
trait CRUDTrait
{
    
    /**
     * @abstract    Load Request Object
     *
     * @param       int|string   $Id               Object id
     *
     * @return      WP_User|bool
     */
    public function load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $User       =       get_user_by("ID", $Id);
        if (is_wp_error($User)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load User (" . $Id . ").");
        }
        return $User;
    }
    
    /**
     * @abstract    Create Request Object
     * @return      WP_User|bool
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["user_email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "user_email");
        }
            
        $UserId = wp_insert_user(array(
            "user_email"    => $this->in["user_email"],
            "user_login"    => ( empty($this->in["user_login"]) ? $this->in["user_email"] : $this->in["user_login"]),
            "user_pass"     => null,
            "role"          => ( isset($this->User_Role) ? $this->User_Role : null)
            ));
        
        if (is_wp_error($UserId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create User. " . $UserId->get_error_message()
            );
        }
        
        return $this->load($UserId);
    }
    
    /**
     * @abstract    Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      int|false
     */
    public function update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($Needed) {
            add_filter('send_email_change_email', '__return_false');
            $UserId = wp_update_user($this->object);
            if (is_wp_error($UserId)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update User. " . $UserId->get_error_message()
                );
            }
            return $UserId;
        }
        return $this->object->ID;
    }
        
    /**
     * @abstract    Delete requested Object
     *
     * @param       int     $Id     Object Id.  If NULL, Object needs to be created.
     *
     * @return      bool
     */
    public function delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        require_once(ABSPATH.'wp-admin/includes/user.php');
        //====================================================================//
        // Delete Object
        $Result = wp_delete_user($Id);
        if (is_wp_error($Result)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Delete User. " . $Result->get_error_message()
            );
        }
        //====================================================================//
        // Delete MultiSite Object
        if (defined("SPLASH_DEBUG") && is_multisite()) {
            require_once ABSPATH . 'wp-admin/includes/ms.php';
            $Result = wpmu_delete_user($Id);
            if (is_wp_error($Result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Delete User. " . $Result->get_error_message()
                );
            }
        }
        return true;
    }
    
    
    /**
     *  @abstract     Common Reading of a User Meta Value
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return       self
     */
    protected function getUserMeta($FieldName)
    {
        $this->out[$FieldName] = get_user_meta($this->object->ID, $FieldName, true);
        return $this;
    }
    
    /**
     *  @abstract     Common Writing of a User Meta Value
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return       self
     */
    protected function setUserMeta($FieldName, $Data)
    {
        //====================================================================//
        //  Compare Field Data
        if (get_user_meta($this->object->ID, $FieldName, true) != $Data) {
            update_user_meta($this->object->ID, $FieldName, $Data);
            $this->needUpdate();
        }
        return $this;
    }
}
