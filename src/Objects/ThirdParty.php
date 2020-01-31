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

namespace   Splash\Local\Objects;

use Splash\Local\Local;
use Splash\Models\AbstractObject;
use Splash\Models\Objects\IntelParserTrait;
use Splash\Models\Objects\SimpleFieldsTrait;

/**
 * Wordpress Customer Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class ThirdParty extends AbstractObject
{
    // Splash Php Core Traits
    use IntelParserTrait;
    use SimpleFieldsTrait;

    // Post Fields
    use \Splash\Local\Objects\Post\CustomTrait;                 // Custom Fields

    // User Fields
    use \Splash\Local\Objects\Users\CRUDTrait;
    use \Splash\Local\Objects\Users\ObjectListTrait;
    use \Splash\Local\Objects\Users\CoreTrait;
    use \Splash\Local\Objects\Users\MainTrait;
    use \Splash\Local\Objects\Users\MetaTrait;
    use \Splash\Local\Objects\Users\AddressTrait;
    use \Splash\Local\Objects\Users\HooksTrait;
    use \Splash\Local\Objects\Users\UserCustomTrait;            // User Custom Fields

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $NAME = "ThirdParty";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "Woocommerce Customer Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-user";

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_CREATED = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var string
     */
    protected $userRole = "customer";

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        /**
         * Check if WooCommerce is active
         */
        if (!Local::hasWooCommerce()) {
            return __("User");
        }

        return self::trans(static::$NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getDesc()
    {
        /**
         * Check if WooCommerce is active
         */
        if (!Local::hasWooCommerce()) {
            return "Wordpress User Object";
        }

        return self::trans(static::$DESCRIPTION);
    }
}
