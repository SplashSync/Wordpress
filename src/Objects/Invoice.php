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

/**
 * WooCommerce Invoice Object (Copy of Orders but Totally ReadOnly)
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Invoice extends Order
{
    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $NAME = "Invoice";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "WooCommerce Virtual Invoice";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-money";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * Allow Creation Of New Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_CREATED = false;

    /**
     * Allow Update Of Existing Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_UPDATED = false;

    /**
     * Allow Delete Of Existing Local Objects
     *
     * {@inheritdoc}
     */
    protected static $ALLOW_PUSH_DELETED = false;

    /**
     * Enable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_CREATED = false;

    /**
     * Enable Update Of Existing Local Objects when Modified Remotly
     *
     * {@inheritdoc}
     */
    //
    protected static $ENABLE_PUSH_UPDATED = false;

    /**
     * Enable Delete Of Existing Local Objects when Deleted Remotly
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_DELETED = false;
}
