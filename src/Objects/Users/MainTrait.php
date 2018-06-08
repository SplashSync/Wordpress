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

/**
 * @abstract    Wordpress Users Main Data Access
 */
trait MainTrait
{
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Main Fields using FieldFactory
    */
    private function buildMainFields()
    {

        //====================================================================//
        // Company
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("user_login")
                ->Name(__("Username"))
                ->MicroData("http://schema.org/Organization", "legalName")
                ->isNotTested();
//                ->isReadOnly();
        
        //====================================================================//
        // Firstname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("first_name")
                ->Name(__("First Name"))
                ->MicroData("http://schema.org/Person", "familyName")
                ->Association("first_name", "last_name")
                ->isListed();
        
        //====================================================================//
        // Lastname
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("last_name")
                ->Name(__("Last Name"))
                ->MicroData("http://schema.org/Person", "givenName")
                ->Association("first_name", "last_name")
                ->isListed();
        
        //====================================================================//
        // WebSite
        $this->fieldsFactory()->Create(SPL_T_URL)
                ->Identifier("user_url")
                ->Name(__("Website"))
                ->MicroData("http://schema.org/Organization", "url");
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
    private function getMainFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'first_name':
            case 'last_name':
                $this->getUserMeta($FieldName);
                break;
            
            case 'user_login':
            case 'user_url':
                $this->getSimple($FieldName);
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
    private function setMainFields($FieldName, $Data)
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case 'first_name':
            case 'last_name':
                $this->setUserMeta($FieldName, $Data);
                break;
            case 'user_login':
            case 'user_url':
                $this->setSimple($FieldName, $Data);
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
}
