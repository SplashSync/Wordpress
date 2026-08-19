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

namespace Splash\Local\Admin;

/**
 * Splash Settings Page Layout Helpers
 *
 * Modern cards layout on top of WP Settings API rendering,
 * adapted from Splash AdvancePack admin screen (prefix spl-wp-).
 * Storage, options keys & fields rendering remain unchanged.
 */
class SettingsLayout
{
    /**
     * Admin Page Hook Suffix of Splash Settings Screen
     */
    private const PAGE_HOOK = "settings_page_splash-wordpress-plugin_settings";

    //====================================================================//
    // Assets Management
    //====================================================================//

    /**
     * Enqueue Settings Page Stylesheet, only on Splash Settings Screen
     */
    public static function enqueueAssets(string $hookSuffix): void
    {
        if (self::PAGE_HOOK !== $hookSuffix) {
            return;
        }
        wp_enqueue_style(
            "splash-settings",
            plugins_url("assets/admin/splash-settings.css", SPLASH_SYNC_PLUGIN_FILE),
            array(),
            SPLASH_SYNC_VERSION
        );
    }

    //====================================================================//
    // Page Scaffolding
    //====================================================================//

    /**
     * Render Page Gradient Header
     */
    public static function getHeader(): string
    {
        $logoUrl = plugins_url("assets/img/splash-ico.png", SPLASH_SYNC_PLUGIN_FILE);

        $html = '<header class="spl-wp-header">';
        $html .= '<div class="spl-wp-header__brand">';
        $html .= '<span class="spl-wp-header__logo">';
        $html .= '<img src="'.esc_url($logoUrl).'" alt="Splash Sync" />';
        $html .= '</span>';
        $html .= '<div>';
        $html .= '<h1 class="spl-wp-header__title">'.esc_html__('Splash Sync', 'splash-wordpress-plugin').'</h1>';
        $html .= '<p class="spl-wp-header__tagline">';
        $html .= esc_html__('Synchronize your WooCommerce data with all your applications.', 'splash-wordpress-plugin');
        $html .= '</p>';
        $html .= '</div>';
        $html .= '</div>';
        $html .= '<span class="spl-wp-header__version">v'.esc_html(SPLASH_SYNC_VERSION).'</span>';
        $html .= '</header>';

        return $html;
    }

    /**
     * Open a Settings Card
     */
    public static function getCardOpen(string $title = "", string $description = "", bool $fullWidth = true): string
    {
        $class = "spl-wp-card".($fullWidth ? " spl-wp-card--full" : "");

        $html = '<section class="'.$class.'">';
        if (!empty($title)) {
            $html .= '<header class="spl-wp-card__head">';
            $html .= '<h2 class="spl-wp-card__title">'.esc_html($title).'</h2>';
            if (!empty($description)) {
                $html .= '<p class="spl-wp-card__desc">'.esc_html($description).'</p>';
            }
            $html .= '</header>';
        }
        $html .= '<div class="spl-wp-card__body">';

        return $html;
    }

    /**
     * Close a Settings Card
     */
    public static function getCardClose(): string
    {
        return '</div></section>';
    }

    /**
     * Open the Cards Grid Container
     */
    public static function getGridOpen(): string
    {
        return '<div class="spl-wp-grid">';
    }

    /**
     * Close the Cards Grid Container
     */
    public static function getGridClose(): string
    {
        return '</div>';
    }
}
