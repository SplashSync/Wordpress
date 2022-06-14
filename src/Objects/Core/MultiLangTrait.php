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

use ArrayObject;
use Splash\Local\Local;

/**
 * WordPress Multi-lang Data Access
 */
trait MultiLangTrait
{
    use WpMultiLangTrait;
    use WpmlTrait;

    /** @var string */
    protected static string $multiLangDisabled = "disabled";
    /** @var string */
    protected static string $multiLangSimulated = "simulated";
    /** @var string */
    protected static string $multiLangWpmu = "WPMU";
    /** @var string */
    protected static string $multiLangWpml = "WPML";

    /** @var null|string */
    private static ?string $mode;

    /**
     * Detect Multi-lang Mode
     *
     * @return string
     */
    public static function multiLangMode(): string
    {
        if (!isset(self::$mode)) {
            self::$mode = self::$multiLangDisabled;
            if (Local::hasWpMultilang()) {
                self::$mode = self::$multiLangWpmu;
            }
            if (Local::hasWpml()) {
                self::$mode = self::$multiLangWpml;
            }
        }

        return self::$mode;
    }

    /**
     * Detect Default Language
     *
     * @return string
     */
    public static function getDefaultLanguage(): string
    {
        static $dfLang;
        if (!isset($dfLang)) {
            $dfLang = get_locale();
            //====================================================================//
            // Wpml Plugin is Enabled
            if (self::multiLangMode() == self::$multiLangWpml) {
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
    public static function isDefaultLanguage(string $isoCode): bool
    {
        return (self::getDefaultLanguage() == $isoCode);
    }

    /**
     * Get Post ID is in Master Language
     *
     * @param int $postId Wp Post ID
     *
     * @return int
     */
    public static function getMultiLangMaster(int $postId): int
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpml) {
            return self::getWpmlMaster($postId);
        }

        return $postId;
    }

    /**
     * Check if Post is in Master Language
     *
     * @param int $postId Wp Post ID
     *
     * @return bool
     */
    public static function isMultiLangMaster(int $postId): bool
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpml) {
            return self::isWpmlMaster($postId);
        }

        return true;
    }

    /**
     * Check if Language allow Writing
     *
     * @param string $languageCode Language Code
     *
     * @return bool
     */
    public static function isWritableLanguage(string $languageCode): bool
    {
        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpml) {
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
     * @return null|string
     */
    public static function getMultiLangFieldName(string $fieldName, string $isoCode): ?string
    {
        //====================================================================//
        // Default Language => No ISO Code on Field Name
        if (self::isDefaultLanguage($isoCode)) {
            return $fieldName;
        }
        //====================================================================//
        // Other Languages => Check if IsoCode is Present in FieldName
        if (false === strpos($fieldName, $isoCode)) {
            return null;
        }
        //====================================================================//
        // Other Languages => Remove ISO Code on Field Name
        return substr($fieldName, 0, strlen($fieldName) - strlen($isoCode) - 1) ?: null;
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
        if (self::multiLangMode() == self::$multiLangDisabled) {
            $result[] = get_locale();
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
            foreach (wpm_get_languages() as $language) {
                if ($language["translation"] != get_locale()) {
                    $result[] = $language["translation"];
                }
            }
        }

        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpml) {
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
        if (self::multiLangMode() == self::$multiLangDisabled) {
            $result[] = get_locale();
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
            foreach (wpm_get_languages() as $language) {
                $result[] = $language["translation"];
            }
        }

        //====================================================================//
        // Wpml Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpml) {
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
     * Read Multi-lang Field
     *
     * @param string $fieldName Field Name
     * @param string $isoCode   Language Iso Code
     * @param string $object    Property Name
     *
     * @return self
     */
    protected function getMultiLang(string $fieldName, string $isoCode, string $object = "object"): self
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
        if (self::multiLangMode() == self::$multiLangWpml) {
            $this->out[$index] = self::getWpmlValue($this->{$object}, $fieldName, $isoCode);

            return $this;
        }
        //====================================================================//
        // Other Cases
        $this->out[$index] = $this->encodeMultiLang($this->{$object}->{$fieldName}, $isoCode);

        return $this;
    }

    /**
     * Write Multi-lang Field
     *
     * @param string          $fieldName Field Name
     * @param string          $isoCode   Language Iso Code
     * @param string|string[] $fieldData Field Data
     * @param string          $object    Property Name
     *
     * @return self
     */
    protected function setMultiLang(string $fieldName, string $isoCode, $fieldData, string $object = "object"): self
    {
        if (method_exists($this, "setSimple")) {
            $this->setSimple(
                $fieldName,
                $this->decodeMultiLang($fieldData, $isoCode, $this->{$object}->{$fieldName}),
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
    protected static function encodeMultiLang(string $fieldData, ?string $isoCode = null)
    {
        //====================================================================//
        // Multi-lang Mode is Disabled
        if (self::multiLangMode() == self::$multiLangDisabled) {
            return $fieldData;
        }
        //====================================================================//
        // Check ISO Code
        if (is_null($isoCode)) {
            $isoCode = self::getDefaultLanguage();
        }
        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
            return self::getWpMuValue($fieldData, $isoCode);
        }

        return $fieldData;
    }

    /**
     * Decode Splash Multi-lang Array into Wp Data
     *
     * @param array|string $fieldData Source Data
     * @param null|string  $isoCode   Language Iso Code
     * @param null|string  $origin    Original Data
     *
     * @return null|string
     */
    protected static function decodeMultiLang($fieldData, string $isoCode = null, ?string $origin = null): ?string
    {
        //====================================================================//
        // MultiLang Mode is Disabled
        if (self::multiLangMode() == self::$multiLangDisabled) {
            return is_string($fieldData) ? $fieldData : "";
        }
        //====================================================================//
        // Check ISO Code
        if (is_null($isoCode)) {
            $isoCode = self::getDefaultLanguage();
        }
        //====================================================================//
        // Wpml Plugin is Enabled
        if (is_string($fieldData) && (self::multiLangMode() == self::$multiLangWpml)) {
            return self::setWpmlValue($fieldData, $isoCode, $origin);
        }
        //====================================================================//
        // Wp MultiLang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
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
        if (self::multiLangMode() == self::$multiLangDisabled) {
            return $fieldData;
        }

        //====================================================================//
        // Multi-lang Mode is Simulated
        if (self::multiLangMode() == self::$multiLangSimulated) {
            return $fieldData;
        }

        //====================================================================//
        // Wp MultiLang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
            return $fieldData;
        }

        //====================================================================//
        // Wp Multi-lang Plugin is Enabled
        if (self::multiLangMode() == self::$multiLangWpmu) {
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
            $originData = self::decodeMultiLang((string) $newData[$isoCode], $isoCode, $originData);
        }

        return (string) $originData;
    }
}
