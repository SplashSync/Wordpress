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

/**
 * WordPress Wp Multi-lang Plugin Trait
 */
trait WpMultiLangTrait
{
    /**
     * Encode WpMultiLang String to Splash MultiLang Array
     *
     * @param string      $input   Generic or Standard Wp MultiLang Data
     * @param null|string $isoCode Language Iso Code
     *
     * @return null|array|string
     */
    protected static function getWpMuValue(string $input, string $isoCode = null)
    {
        //====================================================================//
        // MonoLang => Init Result Array
        if ($isoCode) {
            foreach (wpm_get_languages() as $langKey => $language) {
                if ($language["locale"] == $isoCode) {
                    /** @phpstan-ignore-next-line */
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
     * @param null|string  $origin    Original Wp MultiLang Data
     *
     * @return string
     */
    protected static function setWpMuValue($fieldData, ?string $isoCode, string $origin = null): string
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
