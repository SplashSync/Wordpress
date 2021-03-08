<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
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
use WP_Error;
use WP_User;

/**
 * Wordpress Users CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param int|string $objectId Object id
     *
     * @return bool|WP_User
     */
    public function load($objectId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        $wpUser = get_user_by("ID", $objectId);
        if (is_wp_error($wpUser)) {
            return Splash::log()->errTrace("Unable to load User (".$objectId.").");
        }

        return $wpUser;
    }

    /**
     * Create Request Object
     *
     * @return bool|WP_User
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        //====================================================================//
        // Check Required Fields
        if (empty($this->in["user_email"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "user_email");
        }

        $userId = wp_insert_user(array(
            "user_email" => $this->in["user_email"],
            "user_login" => (empty($this->in["user_login"]) ? $this->in["user_email"] : $this->in["user_login"]),
            "user_pass" => null,
            "role" => (isset($this->userRole) ? $this->userRole : null)
        ));

        if (is_wp_error($userId) || ($userId instanceof WP_Error)) {
            return Splash::log()->errTrace("Unable to Create User. ".$userId->get_error_message());
        }

        return $this->load($userId);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return false|string
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Update User Object
        if ($needed) {
            add_filter('send_email_change_email', '__return_false');
            $userId = wp_update_user($this->object);
            if (is_wp_error($userId) || ($userId instanceof WP_Error)) {
                return Splash::log()->errTrace("Unable to Update User. ".$userId->get_error_message());
            }
        }

        return $this->getObjectIdentifier();
    }

    /**
     * Delete requested Object
     *
     * @param string $objectId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($objectId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        require_once(ABSPATH.'wp-admin/includes/user.php');
        //====================================================================//
        // Delete Object
        $result = wp_delete_user((int) $objectId);
        if (is_wp_error($result)) {
            return Splash::log()->errTrace("Unable to Delete User. ".$result->get_error_message());
        }
        //====================================================================//
        // Delete MultiSite Object
        if (defined("SPLASH_DEBUG") && is_multisite()) {
            require_once ABSPATH.'wp-admin/includes/ms.php';
            $result = wpmu_delete_user((int) $objectId);
            if (is_wp_error($result)) {
                return Splash::log()->errTrace("Unable to Delete User. ".$result->get_error_message());
            }
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->ID)) {
            return false;
        }

        return (string) $this->object->ID;
    }

    /**
     * Common Reading of a User Meta Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getUserMeta($fieldName)
    {
        $this->out[$fieldName] = get_user_meta($this->object->ID, $fieldName, true);

        return $this;
    }

    /**
     * Common Writing of a User Meta Value
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return self
     */
    protected function setUserMeta($fieldName, $fieldData)
    {
        //====================================================================//
        //  Compare Field Data
        if (get_user_meta($this->object->ID, $fieldName, true) != $fieldData) {
            update_user_meta($this->object->ID, $fieldName, $fieldData);
            $this->needUpdate();
        }

        return $this;
    }
}
