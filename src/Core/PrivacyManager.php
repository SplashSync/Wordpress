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

namespace Splash\Local\Core;

use WC_Order;
use WC_Product;
use WP_Post;
use WP_User;

/**
 * Manage Users & Orders Privacy Information
 */
class PrivacyManager
{
    /**
     * Check if a Post Object is Anonymize
     *
     * @param WC_Order|WC_Product|WP_Post|WP_User $object
     *
     * @return bool
     */
    public static function isAnonymize($object): bool
    {
        if (method_exists($object, "get_id")) {
            return self::isAnonymizeById($object->get_id());
        }
        if (isset($object->ID)) {
            return self::isAnonymizeById($object->ID);
        }

        return false;
    }

    /**
     * Check if a Post Object ID is Anonymize
     *
     * @param int $objectId
     *
     * @return bool
     */
    public static function isAnonymizeById(int $objectId): bool
    {
        return ("yes" == get_post_meta($objectId, '_anonymized', true));
    }
}
