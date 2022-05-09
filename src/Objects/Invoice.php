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

/**
 * WooCommerce Invoice Object (Copy of Orders but Totally ReadOnly)
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
    protected static string $name = "Invoice";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $description = "WooCommerce Virtual Invoice";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-money";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushDeleted = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushDeleted = false;
}
