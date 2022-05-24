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

namespace Splash\Local\Objects\Core;

use Splash\Local\Local;

/**
 * WordPress WooCommerce Objects Core Trait
 */
trait WooCommerceObjectTrait
{
    /**
     * {@inheritdoc}
     */
    public static function isDisabled(): bool
    {
        /**
         * Check if WooCommerce is active
         */
        if (!self::hasWooCommerce()) {
            return true;
        }

        return parent::isDisabled();
    }

    /**
     * Check if WooCommerce Plugin is Active
     *
     * @return bool
     */
    public static function hasWooCommerce(): bool
    {
        return Local::hasWooCommerce();
    }
}
