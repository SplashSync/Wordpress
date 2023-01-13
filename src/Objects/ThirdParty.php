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

namespace   Splash\Local\Objects;

use Splash\Local\Local;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\PrimaryKeysAwareInterface;
use Splash\Models\Objects\SimpleFieldsTrait;
use WP_User;

/**
 * WordPress Customer Object
 */
class ThirdParty extends AbstractObject implements PrimaryKeysAwareInterface
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;

    // Post Fields
    use Post\CustomTrait;                 // Custom Fields

    // User Fields
    use Users\CRUDTrait;
    use Users\PrimaryTrait;
    use Users\ObjectListTrait;
    use Users\CoreTrait;
    use Users\MainTrait;
    use Users\MetaTrait;
    use Users\AddressTrait;
    use Users\HooksTrait;
    use Users\UserCustomTrait;            // User Custom Fields

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static string $name = "ThirdParty";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "Woocommerce Customer Object";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-user";

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WP_User;
     */
    protected object $object;

    /**
     * @var string
     */
    protected string $userRole = "customer";

    /**
     * {@inheritdoc}
     */
    public function getName(): string
    {
        /**
         * Check if WooCommerce is active
         */
        if (!Local::hasWooCommerce()) {
            return __("User");
        }

        return self::trans(static::$name);
    }

    /**
     * {@inheritdoc}
     */
    public function getDesc(): string
    {
        /**
         * Check if WooCommerce is active
         */
        if (!Local::hasWooCommerce()) {
            return "Wordpress User Object";
        }

        return self::trans(static::$description);
    }
}
