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

use Splash\Client\Splash;

/**
 * @abstract    Wordpress Wp Multilang Plugin Trait
 */
trait WpMultilangTrait
{
    
    /**
     * @abstract    Check if WpMultilang Plugin is Active
     *
     * @return  bool
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
        return in_array('wp-multilang/wp-multilang.php', apply_filters('active_plugins', get_option('active_plugins')));
    }
    
    /**
     * @abstract    Encode WpMultilang String to Splash Multilang Array
     * @param       string  $Input      Generic or Standard Wp Multilang Data
     * @return      array
     */
    protected function getWpMuValue($Input)
    {
        //====================================================================//
        // Init Result Array
        $Output  =  array();
        // Add Available Languages
        foreach (wpm_get_languages() as $LangKey => $Language) {
            $Output[$Language["locale"]]    =   wpm_translate_string($Input, $LangKey);
        }
        return $Output;
    }

    /**
     * @abstract    Decode Splash Multilang Array and update WpMultilang String
     * @param       string  $Input      Generic or Standard Wp Multilang Data
     * @param       array   $Data       Splash Multilang Field Data
     * @return      bool                Data was Updated
     */
    protected function setWpMuValue(&$Input, $Data)
    {
        $Origin =   $Input;
        //====================================================================//
        // For Each Available Languages
        foreach (wpm_get_languages() as $LangKey => $Language) {
            if (!isset($Data[$Language["locale"]]) || !is_scalar($Data[$Language["locale"]])) {
                continue;
            }
            //====================================================================//
            // Update Multilang Value
            $Input = wpm_set_new_value(
                $Input,
                $Data[$Language["locale"]],
                [],
                $LangKey
            );
        }
        if ($Origin != $Input) {
            $this->needUpdate();
            return true;
        }
        return false;
    }
}
