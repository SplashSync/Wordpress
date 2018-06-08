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

namespace Splash\Local\Objects\Users;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    Wordpress Users Core Data Access
 */
trait CoreTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Core Fields using FieldFactory
    */
    private function buildCoreFields()
    {

        global $wp_roles;
        
        //====================================================================//
        // Email
        $this->fieldsFactory()->Create(SPL_T_EMAIL)
                ->Identifier("user_email")
                ->Name(__("Email"))
                ->MicroData("http://schema.org/ContactPoint", "email")
                ->isRequired()
                ->isListed();
        
        //====================================================================//
        // User Role
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("roles")
                ->Name(__("Role"))
                ->MicroData("http://schema.org/Person", "jobTitle")
                ->isListed()
                ->AddChoices($wp_roles->get_names());
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
     *  @return         none
     */
    private function getCoreFields($Key, $FieldName)
    {
        
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'user_email':
                $this->getSimple($FieldName);
                break;
            
            case 'roles':
                $UserRoles  =    $this->Object->roles;
                $this->Out[$FieldName] = array_shift($UserRoles);
                break;
            
            default:
                return;
        }
        
        unset($this->In[$Key]);
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
     *  @return         none
     */
    private function setCoreFields($FieldName, $Data)
    {
        global $wp_roles;
        
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case 'user_email':
                $this->setSimple($FieldName, $Data);
                break;

            case 'roles':
                // Duplicate User Role Array
                $UserRoles  =    $this->Object->roles;
                // No Changes
                if (array_shift($UserRoles) === $Data) {
                    break;
                }
                // Validate Role
                $Roles = $wp_roles->get_names();
                if (!isset($Roles[$Data])) {
                    Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Requested User Role Doesn't Exists.");
                    return;
                }
                $this->Object->set_role($Data);
                $this->needUpdate();
                break;
            
            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
}
