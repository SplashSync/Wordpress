<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Core;

use Splash\Core\SplashCore      as Splash;

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
     * @abstract        Detect Mulilang Mode
     *
     * @return          string
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
     * @abstract        Detect Available Languages
     *
     * @return          array
     */
    public static function getAvailablelanguages()
    {
        
        $Result =   array();
    
        // Multilang Mode is Disabled
        if (self::multilangMode() == self::$MULTILANG_DISABLED) {
            $Result[]   =   get_locale();
        }
        
        // Multilang Mode is Simulated
        if (self::multilangMode() == self::$MULTILANG_SIMULATED) {
            $Result[]   =   get_locale();
        }

        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            foreach (wpm_get_languages() as $Languange) {
                $Result[]   =   $Languange["translation"];
            }
        }

        return $Result;
    }
    
    /**
     * @abstract    Read Mulilang Field
     * @param       string      $FieldName
     * @param       string      $Object
     * @return      self
     */
    protected function getMultilangual($FieldName, $Object = "Object")
    {
        $this->Out[$FieldName]  =   $this->encodeMultilang($this->$Object->$FieldName);
        return $this;
    }

    /**
     * @abstract    Write Mulilang Field
     * @param       string      $FieldName
     * @param       mixed       $Data
     * @param       string      $Object
     * @return      self
     */
    protected function setMultilangual($FieldName, $Data, $Object = "Object")
    {
        $this->setSimple($FieldName, $this->decodeMultilang($Data, $this->$Object->$FieldName), $Object);
        return $this;
    }
    
    /**
     * @abstract    Build Splash Multilang Array for Given Data
     * @param       string      $Data           Source Data
     * @return      array
     */
    protected function encodeMultilang($Data)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $Data;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            return array(
                get_locale()    =>  $Data
            );
        }
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->getWpMuValue($Data);
        }
        return null;
    }
    
    /**
     * @abstract    Decode Splash Multilang Array into Wp Data
     * @param       array   $Data           Source Data
     * @param       string  $Origin         Original Data
     * @return      string
     */
    protected function decodeMultilang($Data, $Origin = null)
    {

        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $Data;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            if (isset($Data[get_locale()])) {
                return $Data[get_locale()];
            }
        }

        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            return $this->setWpMuValue($Data, $Origin);
        }
        
        return null;
    }
    
    /**
     * @abstract        Extract Mulilang Field value
     * @param   string  $Data           Source Data
     * @param   string  $Language       ISO Language Code
     * @return          string
     */
    protected function extractMultilangValue($Data, $Language = null)
    {
        //====================================================================//
        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            return $Data;
        }
        
        //====================================================================//
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            return $Data;
        }
        
        //====================================================================//
        // Wp Multilang Plugin is Enabled
        if (self::multilangMode() == self::$MULTILANG_WPMU) {
            $MultilangArray     =   $this->getWpMuValue($Data);
            if (empty($Language) && isset($MultilangArray[get_locale()])) {
                return $MultilangArray[get_locale()];
            } elseif (isset($MultilangArray[$Language])) {
                return $MultilangArray[$Language];
            }
        }
        
        return null;
    }
}
