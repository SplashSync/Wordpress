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

namespace Splash\Local\Objects\Post;

/**
 * Wordpress Custom Fields Data Access
 */
trait CustomTrait
{
    
    private $CustomPrefix = "custom_";
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Custom Data Fields using FieldFactory
    */
    private function buildCustomFields()
    {

        //====================================================================//
        // Require Posts Functions
        require_once(ABSPATH . "wp-admin/includes/post.php");

        //====================================================================//
        // Load List of Custom Fields
        $MetaKeys = get_meta_keys();
        
            
        //====================================================================//
        // Filter List of Custom Fields
        foreach ($MetaKeys as $Index => $Key) {
            //====================================================================//
            // Filter Protected Fields
            if (is_protected_meta($Key)) {
                unset($MetaKeys[ $Index ]);
            }
            //====================================================================//
            // Filter Splash Fields
            if (( $Key == "splash_id") || ( $Key == "splash_origin")) {
                unset($MetaKeys[ $Index ]);
            }
        }
        
        //====================================================================//
        // Create Custom Fields Definitions
        foreach ($MetaKeys as $Key) {
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                    ->Identifier($this->CustomPrefix . $Key)
                    ->Name(ucwords($Key))
                    ->Group("Custom")
                    ->MicroData("http://meta.schema.org/additionalType", $Key);
            
            //====================================================================//
            // Filter Products Attributes Fields
            if (strpos($Key, "attribute_pa") !== false) {
                $this->fieldsFactory()->isReadOnly();
            }
        }
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return       void
     */
    private function getCustomFields($Key, $FieldName)
    {
        //====================================================================//
        // Filter Field Id
        if (strpos($FieldName, $this->CustomPrefix) !== 0) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $MetaFieldName = substr($FieldName, strlen($this->CustomPrefix));
        //====================================================================//
        // Read Field Data
        $this->out[$FieldName] = get_post_meta($this->object->ID, $MetaFieldName, true);
        
        unset($this->in[$Key]);
    }
        
    //====================================================================//
    // Fields Writting Functions
    //====================================================================//
      
    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return       void
     */
    private function setCustomFields($FieldName, $Data)
    {
        //====================================================================//
        // Filter Field Id
        if (strpos($FieldName, $this->CustomPrefix) !== 0) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $MetaFieldName = substr($FieldName, strlen($this->CustomPrefix));
        //====================================================================//
        // Write Field Data
        if (get_post_meta($this->object->ID, $MetaFieldName, true) != $Data) {
            update_post_meta($this->object->ID, $MetaFieldName, $Data);
            $this->needUpdate();
        }
        unset($this->in[$FieldName]);
    }
}
