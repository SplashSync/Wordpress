<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
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

/**
 * Wordpress Customer Address CRUD Functions
 */
trait CRUDTrait
{
    use UserCRUDTrait;
    
    /**
     * Load Request Object
     *
     * @param int|string $postId Object id
     *
     * @return mixed
     */
    public function load($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Decode Address User Id
        $userId = $this->decodeUserId((string) $postId);
        //====================================================================//
        // Init Object
        $wpUser       =       get_user_by("ID", (string) $userId);
        if (is_wp_error($wpUser)) {
            return Splash::log()->errTrace("Unable to load User for Address (" . $postId . ").");
        }

        return $wpUser;
    }
    
    /**
     * Create Request Object
     *
     * @return false|object
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Not Allowed
        return Splash::log()->errTrace("Creation of Customer Address Not Allowed.");
    }
        
    /**
     * Delete requested Object
     *
     * @param int $postId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function delete($postId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Not Allowed
        return Splash::log()->warTrace("Delete of Customer Address Not Allowed.");
    }
}
