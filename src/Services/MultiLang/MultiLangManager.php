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

namespace Splash\Local\Services\MultiLang;

use Splash\Local\Interfaces\MultiLangProviderInterface;
use Splash\Local\Local;

/**
 * Multi-lang Plugins Manager
 *
 * Detects the active Multi-lang plugin once and gives access to its provider.
 * Priority when several are active: WPML, then Polylang, then WP Multilang.
 */
class MultiLangManager
{
    /**
     * Active Multi-lang Provider
     *
     * @var null|MultiLangProviderInterface
     */
    private static ?MultiLangProviderInterface $provider = null;

    /**
     * Get Active Multi-lang Provider
     *
     * @return MultiLangProviderInterface
     */
    public static function getProvider(): MultiLangProviderInterface
    {
        if (!isset(self::$provider)) {
            self::$provider = self::detectProvider();
        }

        return self::$provider;
    }

    /**
     * Force Active Multi-lang Provider, or reset detection with null
     *
     * @param null|MultiLangProviderInterface $provider
     *
     * @return void
     */
    public static function setProvider(?MultiLangProviderInterface $provider): void
    {
        self::$provider = $provider;
    }

    /**
     * Detect Active Multi-lang Plugin
     *
     * @return MultiLangProviderInterface
     */
    private static function detectProvider(): MultiLangProviderInterface
    {
        if (Local::hasWpml()) {
            return new WpmlProvider();
        }
        if (Local::hasPolylang()) {
            return new PolylangProvider();
        }
        if (Local::hasWpMultilang()) {
            return new WpMultilangProvider();
        }

        return new DisabledProvider();
    }
}
