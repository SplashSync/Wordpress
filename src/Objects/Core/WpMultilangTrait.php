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

namespace Splash\Local\Objects\Core;

/**
 * Wordpress Wp Multilang Plugin Trait
 */
trait WpMultilangTrait
{
    /**
     * Check if WpMultilang Plugin is Active
     *
     * @return bool
     */
    public static function hasWpMultilang()
    {
        //====================================================================//
        // Check at Network Level
        if (is_multisite()) {
            if (array_key_exists('wp-multilang/wp-multilang.php', get_site_option('active_sitewide_plugins'))) {
                return true;
            }
        }
        //====================================================================//
        // Check at Site Level
        return in_array(
            'wp-multilang/wp-multilang.php',
            apply_filters('active_plugins', get_option('active_plugins')),
            true
        );
    }
    
    /**
     * Encode WpMultilang String to Splash Multilang Array
     *
     * @param string $input Generic or Standard Wp Multilang Data
     *
     * @return array
     */
    protected function getWpMuValue($input)
    {
        //====================================================================//
        // Init Result Array
        $output  =  array();
        // Add Available Languages
        foreach (wpm_get_languages() as $langKey => $language) {
            $output[$language["locale"]]    =   wpm_translate_string($input, $langKey);
        }

        return $output;
    }

    /**
     * Decode Splash Multilang Array and update WpMultilang String
     *
     * @param array  $fieldData Splash Multilang Field Data
     * @param string $origin    Original Wp Multilang Data
     *
     * @return bool Data was Updated
     */
    protected function setWpMuValue($fieldData, $origin = null)
    {
        //====================================================================//
        // For Each Available Languages
        foreach (wpm_get_languages() as $langKey => $language) {
            if (!isset($fieldData[$language["locale"]]) || !is_scalar($fieldData[$language["locale"]])) {
                continue;
            }
            //====================================================================//
            // Update Multilang Value
            $origin = wpm_set_new_value(
                $origin,
                $fieldData[$language["locale"]],
                array(),
                $langKey
            );
        }

        return $origin;
    }
}
