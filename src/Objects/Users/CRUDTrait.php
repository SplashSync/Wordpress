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
use WP_Error;
use WP_User;

/**
 * WordPress Users CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $objectId Object id
     *
     * @return null|WP_User
     */
    public function load(string $objectId): ?WP_User
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        $wpUser = get_user_by("ID", $objectId);
        if (is_wp_error($wpUser)) {
            Splash::log()->errTrace("Unable to load User (".$objectId.").");

            return null;
        }

        return $wpUser ?: null;
    }

    /**
     * Create Request Object
     *
     * @return null|WP_User
     */
    public function create(): ?WP_User
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["user_email"])) {
            return Splash::log()->errNull(
                "ErrLocalFieldMissing",
                __CLASS__,
                __FUNCTION__,
                "user_email"
            );
        }
        //====================================================================//
        // Create new User
        $userId = wp_insert_user(array(
            "user_email" => $this->in["user_email"],
            "user_login" => (empty($this->in["user_login"]) ? $this->in["user_email"] : $this->in["user_login"]),
            "user_pass" => null,
            "role" => ($this->userRole ?: null)
        ));
        if (is_wp_error($userId) || ($userId instanceof WP_Error)) {
            Splash::log()->errTrace("Unable to Create User. ".$userId->get_error_message());

            return null;
        }

        return $this->load((string) $userId);
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string
     */
    public function update(bool $needed): ?string
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
                Splash::log()->errTrace("Unable to Update User. ".$userId->get_error_message());

                return null;
            }
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $objectId): bool
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
    public function getObjectIdentifier(): ?string
    {
        if (empty($this->object->ID)) {
            return null;
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
    protected function getUserMeta(string $fieldName): self
    {
        /** @phpstan-ignore-next-line  */
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
    protected function setUserMeta(string $fieldName, $fieldData): self
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
