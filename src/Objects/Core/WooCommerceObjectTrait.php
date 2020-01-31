<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Core;

use Splash\Local\Local;

/**
 * Wordpress WooCommerce Objects Core Trait
 */
trait WooCommerceObjectTrait
{
    /**
     * Return Object Status
     *
     * @return bool
     *
     * @SuppressWarnings(PHPMD.BooleanGetMethodName)
     */
    public static function getIsDisabled()
    {
        /**
         * Check if WooCommerce is active
         */
        if (!self::hasWooCommerce()) {
            return true;
        }

        return static::$DISABLED;
    }

    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerce()
    {
        return Local::hasWooCommerce();
    }
}
