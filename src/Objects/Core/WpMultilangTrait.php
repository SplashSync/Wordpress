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
     * Encode WpMultiLang String to Splash MultiLang Array
     *
     * @param string $input   Generic or Standard Wp MultiLang Data
     * @param string $isoCode Language Iso Code
     *
     * @return null|array|string
     */
    protected static function getWpMuValue($input, $isoCode = null)
    {
        //====================================================================//
        // MonoLang => Init Result Array
        if ($isoCode) {
            foreach (wpm_get_languages() as $langKey => $language) {
                if ($language["locale"] == $isoCode) {
                    return wpm_translate_string($input, $langKey);
                }
            }

            return null;
        }

        //====================================================================//
        // MultiLang => Init Result Array
        $output = array();
        // Add Available Languages
        foreach (wpm_get_languages() as $langKey => $language) {
            $output[$language["locale"]] = wpm_translate_string($input, $langKey);
        }

        return $output;
    }

    /**
     * Decode Splash MultiLang Array and update WpMultiLang String
     *
     * @param array|string $fieldData Splash MultiLang Field Data
     * @param null|string  $isoCode   Language Iso Code
     * @param string       $origin    Original Wp MultiLang Data
     *
     * @return string
     */
    protected static function setWpMuValue($fieldData, $isoCode, $origin = null)
    {
        if (is_string($fieldData) && !is_null($isoCode)) {
            foreach (wpm_get_languages() as $langKey => $language) {
                if ($language["locale"] == $isoCode) {
                    //====================================================================//
                    // Update MultiLang Value
                    /** @var string $origin */
                    $origin = wpm_set_new_value(
                        $origin,
                        $fieldData,
                        array(),
                        $langKey
                    );
                }
            }

            return (string) $origin;
        }

        if (is_array($fieldData)) {
            //====================================================================//
            // MultiLang => For Each Available Languages
            foreach (wpm_get_languages() as $langKey => $language) {
                /** @var string $locale */
                $locale = $language["locale"];
                if (!isset($fieldData[$locale]) || !is_scalar($fieldData[$locale])) {
                    continue;
                }
                //====================================================================//
                // Update MultiLang Value
                /** @var string $origin */
                $origin = wpm_set_new_value(
                    $origin,
                    $fieldData[$locale],
                    array(),
                    $langKey
                );
            }
        }

        return (string) $origin;
    }
}
