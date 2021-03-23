<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Core;

use ArrayObject;
use Splash\Local\Local;

/**
 * Wordpress Multilang Data Access
 */
trait MultilangTrait
{
    use WpMultilangTrait;
    use WpmlTrait;

    /** @var string */
    protected static $MULTILANG_DISABLED = "disabled";
    /** @var string */
    protected static $MULTILANG_SIMULATED = "simulated";
    /** @var string */
    protected static $MULTILANG_WPMU = "WPMU";
    /** @var string */
    protected static $MULTILANG_WPML = "WPML";

    /** @var null|string */
    private static $mode;

    /**
     * Detect Muli-lang Mode
     *
     * @return string
     */
    public static function multilangMode()
    {
        if (!isset(static::$mode)) {
            static::$mode = self::$MULTILANG_DISABLED;
            if (Local::hasWpMultilang()) {
                static::$mode = self::$MULTILANG_WPMU;
            }
            if (Local::hasWpml()) {
                static::$mode = self::$MULTILANG_WPML;
            }
        }

        return static::$mode;
    }

    /**
     * Detect Default Language
     *
     * @return string
     */
    public static function getDefaultLanguage()
    {
        static $dfLang;
        if (!isset($dfLang)) {
            $dfLang = get_locale();
            //====================================================================//
            // Wpml Plugin is Enabled
            if (self::multilangMode() == self::$MULTILANG_WPML) {
                $dfLang = self::getWpmlDefaultLanguage();
            }
        }

        return $dfLang;
    }

    /**
     * Detect Default Language
     *
     * @param string $isoCode Language Iso Code
     *
     * @return bool
     */
    public static function isDefaultLanguage($isoCode)
    {
        return (self::getDefaultLanguage() == $isoCode);
    }

    /**
     * Get Post ID is in Master Language
     *
     * @param int $postId Wp Post Id
     *
     * @return int
     */
    public static function getMultiLangMaster(int $postId): int
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            return self::getWpmlMaster($postId);
        }

        return $postId;
    }

    /**
     * Check if Post is in Master Language
     *
     * @param int $postId Wp Post Id
     *
     * @return bool
     */
    public static function isMultiLangMaster(int $postId): bool
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            return self::isWpmlMaster($postId);
        }

        return true;
    }

    /**
     * Check if Language allow Write
     *
     * @param string $languageCode Language Code
     *
     * @return bool
     */
    public static function isWritableLanguage(string $languageCode): bool
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            return self::isDefaultLanguage($languageCode);
        }

        return true;
    }

    /**
     * Reduce Field Name Based on Language Code
     *
     * @param string $fieldName Origine Field Name
     * @param string $isoCode   Language Iso Code
     *
     * @return false|string
     */
    public static function getMultiLangFieldName($fieldName, $isoCode)
    {
        //====================================================================//
        // Default Language => No ISO Code on Field Name
        if (self::isDefaultLanguage($isoCode)) {
            return $fieldName;
        }
        //====================================================================//
        // Other Languages => Check if IsoCode is Present in FieldName
        if (false === strpos($fieldName, $isoCode)) {
            return false;
        }
        //====================================================================//
        // Other Languages => Remove ISO Code on Field Name
        return substr($fieldName, 0, strlen($fieldName) - strlen($isoCode) - 1);
    }

    /**
     * Detect Additional Languages
     *
     * @return array
     */
    public static function getExtraLanguages()
    {
        $result = array();

        //====================================================================//
        // Multi-lang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $result[] = get_locale();
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            foreach (wpm_get_languages() as $language) {
                if ($language["translation"] != get_locale()) {
                    $result[] = $language["translation"];
                }
            }
        }

        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            $languages = apply_filters('wpml_active_languages', null, 'orderby=id');
            if (is_array($languages)) {
                foreach ($languages as $language) {
                    if ($language["language_code"] != get_locale()) {
                        $result[] = $language["language_code"];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * Detect Available Languages
     *
     * @return array
     */
    public static function getAvailableLanguages()
    {
        $result = array();

        //====================================================================//
        // Multi-lang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $result[] = get_locale();
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            foreach (wpm_get_languages() as $language) {
                $result[] = $language["translation"];
            }
        }

        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            $languages = apply_filters('wpml_active_languages', null, 'orderby=id');
            if (is_array($languages)) {
                foreach ($languages as $language) {
                    $result[] = $language["language_code"];
                }
            }
        }

        return $result;
    }

    /**
     * Read Mulilang Field
     *
     * @param string $fieldName Field Name
     * @param string $isoCode   Language Iso Code
     * @param string $object    Property Name
     *
     * @return self
     */
    protected function getMultilangual($fieldName, $isoCode, $object = "object")
    {
        //====================================================================//
        // Safety Check
        if (!isset($this->out)) {
            return $this;
        }
        //====================================================================//
        // Redirect to Multi-lang Index
        $index = self::isDefaultLanguage($isoCode) ? $fieldName : $fieldName."_".$isoCode;
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPML) {
            $this->out[$index] = self::getWpmlValue($this->{$object}, $fieldName, $isoCode);

            return $this;
        }
        //====================================================================//
        // Other Cases
        $this->out[$index] = $this->encodeMultiLang($this->{$object}->{$fieldName}, $isoCode);

        return $this;
    }

    /**
     * Write Mulilang Field
     *
     * @param string $fieldName Field Name
     * @param string $isoCode   Language Iso Code
     * @param mixed  $fieldData Field Data
     * @param string $object    Property Name
     *
     * @return self
     */
    protected function setMultilangual($fieldName, $isoCode, $fieldData, $object = "object")
    {
        if (method_exists($this, "setSimple")) {
            $this->setSimple(
                $fieldName,
                $this->decodeMultilang($fieldData, $isoCode, $this->{$object}->{$fieldName}),
                $object
            );
        }

        return $this;
    }

    /**
     * Build Splash Multi-lang Array for Given Data
     *
     * @param string      $fieldData Source Data
     * @param null|string $isoCode   Language Iso Code
     *
     * @return null|array|string
     */
    protected static function encodeMultiLang($fieldData, $isoCode = null)
    {
        //====================================================================//
        // Multi-lang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        //====================================================================//
        // Check ISO Code
        if (is_null($isoCode)) {
            $isoCode = self::getDefaultLanguage();
        }
        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return self::getWpMuValue($fieldData, $isoCode);
        }

        return null;
    }

    /**
     * Decode Splash Multi-lang Array into Wp Data
     *
     * @param array|string $fieldData Source Data
     * @param null|string  $isoCode   Language Iso Code
     * @param string       $origin    Original Data
     *
     * @return null|string
     */
    protected static function decodeMultilang($fieldData, $isoCode = null, $origin = null)
    {
        //====================================================================//
        // MultiLang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            return is_string($fieldData) ? $fieldData : "";
        }
        //====================================================================//
        // Check ISO Code
        if (is_null($isoCode)) {
            $isoCode = self::getDefaultLanguage();
        }
        //====================================================================//
        // Wpml Plugin is Enabled
        if (is_string($fieldData) && (self::multilangMode() == self::$MULTILANG_WPML)) {
            return self::setWpmlValue($fieldData, $isoCode, $origin);
        }
        //====================================================================//
        // Wp MultiLang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return self::setWpMuValue($fieldData, $isoCode, $origin);
        }

        return null;
    }

    /**
     * Extract Multi-lang Field value
     *
     * @param string $fieldData Source Data
     * @param string $language  ISO Language Code
     *
     * @return null|string
     */
    protected function extractMultilangValue($fieldData, $language = null)
    {
        //====================================================================//
        // Multi-lang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }

        //====================================================================//
        // Multi-lang Mode is Simulated
        if (self::multilangMode() == self::$MULTILANG_SIMULATED) {
            return $fieldData;
        }

        //====================================================================//
        // Wp MultiLang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $fieldData;
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            /** @var array $multilangArray */
            $multilangArray = $this->getWpMuValue($fieldData);
            if (empty($language) && isset($multilangArray[get_locale()])) {
                return $multilangArray[get_locale()];
            }
            if (isset($multilangArray[$language])) {
                return $multilangArray[$language];
            }
        }

        return null;
    }

    /**
     * Build Multilang Array from Fields Array
     *
     * @param array|ArrayObject $fieldData Source Data
     * @param string            $fieldName Base Name (Array Key) for Field to Detect
     *
     * @return array
     */
    protected static function buildMultilangArray($fieldData, $fieldName)
    {
        //====================================================================//
        // Init Result Array
        $response = array();
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Reduce Multilang Field Name
            $index = self::isDefaultLanguage($isoCode) ? $fieldName : $fieldName."_".$isoCode;
            //====================================================================//
            // Check if Field Value Exists
            if (!isset($fieldData[$index]) || !is_scalar($fieldData[$index])) {
                continue;
            }
            //====================================================================//
            // Add Value To Results
            $response[$isoCode] = $fieldData[$index];
        }

        return $response;
    }

    /**
     * Update field value usiong Multilang Array
     *
     * @param string $originData Original Source Data (Raw String)
     * @param array  $newData    New Data (Multilang Array)
     *
     * @return string
     */
    protected static function applyMultilangArray($originData, $newData)
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Check if Field Value Exists
            if (!isset($newData[$isoCode]) || !is_scalar($newData[$isoCode])) {
                continue;
            }
            $originData = self::decodeMultilang((string) $newData[$isoCode], $isoCode, $originData);
        }

        return (string) $originData;
    }
}
