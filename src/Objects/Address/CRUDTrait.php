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

use Splash\Core\SplashCore                  as Splash;
use Splash\Local\Objects\Users\CRUDTrait    as UserCRUDTrait;
use WP_User;

/**
 * WordPress Customer Address CRUD Functions
 */
trait CRUDTrait
{
    use UserCRUDTrait;

    /**
     * {@inheritdoc}
     */
    public function load(string $postId): ?WP_User
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Decode Address User Id
        $userId = $this->decodeUserId((string) $postId);
        //====================================================================//
        // Init Object
        $wpUser = get_user_by("ID", (string) $userId);
        if (is_wp_error($wpUser)) {
            Splash::log()->errTrace("Unable to load User for Address (".$postId.").");

            return null;
        }

        return $wpUser ?: null;
    }

    /**
     * {@inheritdoc}
     */
    public function create(): ?WP_User
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Not Allowed
        return Splash::log()->errNull("Creation of Customer Address Not Allowed.");
    }

    /**
     * {@inheritdoc}
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete(string $postId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Not Allowed
        return Splash::log()->warTrace("Delete of Customer Address Not Allowed.");
    }
}
