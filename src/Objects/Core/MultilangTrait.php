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
 * Wordpress Multilang Data Access
 */
trait MultilangTrait
{
    use WpMultilangTrait;
    
    protected static $MULTILANG_DISABLED         =   "disabled";
    protected static $MULTILANG_SIMULATED        =   "simulated";
    protected static $MULTILANG_WPMU             =   "WPMU";

    /**
     * Detect Mulilang Mode
     *
     * @return string
     */
    public static function multilangMode()
    {
        if (self::hasWpMultilang()) {
            return   self::$MULTILANG_WPMU;
        }
        
        return self::$MULTILANG_DISABLED;
    }
    
    /**
     * Detect Default Language
     *
     * @return string
     */
    public static function getDefaultLanguage()
    {
        return get_locale();
    }

    /**
     * Detect Default Language
     *
     * @param string $isoCode   Language Iso Code
     *
     * @return string
     */
    public static function isDefaultLanguage($isoCode)
    {
        return (get_locale() == $isoCode);
    }
    
    /**
     * Reduce Field Name Based on Language Code
     *
     * @param string $fieldName Origine Field Name
     * @param string $isoCode   Language Iso Code
     *
     * @return false|string
     */
    public static function getMultilangFieldName($fieldName, $isoCode)
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
     * Detect Additionnal Languages
     *
     * @return array
     */
    public static function getExtraLanguages()
    {
        $result =   array();
    
        // Multilang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $result[]   =   get_locale();
        }
        
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            foreach (wpm_get_languages() as $language) {
                if ($language["translation"] != get_locale()) {
                    $result[]   =   $language["translation"];
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
        $result =   array();
    
        // Multilang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $result[]   =   get_locale();
        }

        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            foreach (wpm_get_languages() as $language) {
                $result[]   =   $language["translation"];
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
        if (!isset($this->out)) {
            return $this;
        }
        
        if (self::isDefaultLanguage($isoCode)) {
            $this->out[$fieldName]  =   $this->encodeMultilang($this->{$object}->{$fieldName}, $isoCode);

            return $this;
        }

        $this->out[$fieldName . "_" . $isoCode]  =   $this->encodeMultilang($this->{$object}->{$fieldName}, $isoCode);

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
        $this->setSimple(
            $fieldName,
            $this->decodeMultilang($fieldData, $isoCode, $this->{$object}->{$fieldName}),
            $object
        );

        return $this;
    }
    
    /**
     * Build Splash Multilang Array for Given Data
     *
     * @param string $fieldData Source Data
     * @param string $isoCode   Language Iso Code
     *
     * @return null|array|string
     */
    protected function encodeMultilang($fieldData, $isoCode)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->getWpMuValue($fieldData, $isoCode);
        }

        return null;
    }
    
    /**
     * Decode Splash Multilang Array into Wp Data
     *
     * @param array|string $fieldData Source Data
     * @param string       $isoCode   Language Iso Code
     * @param string       $origin    Original Data
     *
     * @return null|string
     */
    protected function decodeMultilang($fieldData, $isoCode, $origin = null)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->setWpMuValue($fieldData, $isoCode, $origin);
        }
        
        return null;
    }
    
    /**
     * Extract Mulilang Field value
     *
     * @param string $fieldData Source Data
     * @param string $language  ISO Language Code
     *
     * @return null|string
     */
    protected function extractMultilangValue($fieldData, $language = null)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            $multilangArray     =   $this->getWpMuValue($fieldData);
            if (empty($language) && isset($multilangArray[get_locale()])) {
                return $multilangArray[get_locale()];
            }
            if (isset($multilangArray[$language])) {
                return $multilangArray[$language];
            }
        }
        
        return null;
    }
}
