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
        
        if (get_option("splash_multilang")) {
            return   self::$MULTILANG_SIMULATED;
        }
        
        return self::$MULTILANG_DISABLED;
    }
    
    /**
     * Detect Available Languages
     *
     * @return array
     */
    public static function getAvailablelanguages()
    {
        $result =   array();
    
        // Multilang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $result[]   =   get_locale();
        }
        
        // Multilang Mode is Simulated
        if (self::multilangMode() == self::$MULTILANG_SIMULATED) {
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
     * @param string $fieldName
     * @param string $object
     *
     * @return self
     */
    protected function getMultilangual($fieldName, $object = "object")
    {
        if (isset($this->out)) {
            $this->out[$fieldName]  =   $this->encodeMultilang($this->{$object}->{$fieldName});
        }

        return $this;
    }

    /**
     * Write Mulilang Field
     *
     * @param string $fieldName
     * @param mixed  $fieldData
     * @param string $object
     *
     * @return self
     */
    protected function setMultilangual($fieldName, $fieldData, $object = "object")
    {
        $this->setSimple($fieldName, $this->decodeMultilang($fieldData, $this->{$object}->{$fieldName}), $object);

        return $this;
    }
    
    /**
     * Build Splash Multilang Array for Given Data
     *
     * @param string $fieldData Source Data
     *
     * @return null|array|string
     */
    protected function encodeMultilang($fieldData)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            return array(
                get_locale()    =>  $fieldData
            );
        }
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->getWpMuValue($fieldData);
        }

        return null;
    }
    
    /**
     * Decode Splash Multilang Array into Wp Data
     *
     * @param array|string $fieldData Source Data
     * @param string       $origin    Original Data
     *
     * @return null|string
     */
    protected function decodeMultilang($fieldData, $origin = null)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $fieldData;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            if (isset($fieldData[get_locale()])) {
                return $fieldData[get_locale()];
            }
        }

        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->setWpMuValue($fieldData, $origin);
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
