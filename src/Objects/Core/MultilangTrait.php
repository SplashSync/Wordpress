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
    
    static $MULTILANG_DISABLED         =   "disabled";
    static $MULTILANG_SIMULATED        =   "simulated";

    /**
     * @abstract        Detect Mulilang Mode
     *
     * @return          string
     */
    public static function multilangMode()
    {
        
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
        
        return $Result;
    }
    
    /**
     * @abstract        Read Mulilang Field
     *
     * @return          string
     */
    protected function getMultilangual($FieldName)
    {

        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            $this->getSimple($FieldName);
            return $this;
        }
        
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            $this->Out[$FieldName]  =   array(
                get_locale()    =>  $this->Object->$FieldName
            );
            return $this;
        }
        
        return $this;
    }
    
    /**
     * @abstract        Read Mulilang Field
     *
     * @return          string
     */
    protected function setMultilangual($FieldName, $Data)
    {

        // Multilang Mode is Disabled
        if ($this->multilangMode() == self::$MULTILANG_DISABLED) {
            $this->setSimple($FieldName, $Data);
            return $this;
        }
        
        // Multilang Mode is Simulated
        if ($this->multilangMode() == self::$MULTILANG_SIMULATED) {
            if (!isset($Data[get_locale()])) {
                return $this;
            }
            
            $this->setSimple($FieldName, $Data[get_locale()]);
        }
        
        return $this;
    }
}
