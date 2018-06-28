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
 * @abstract    Wordpress Core Data Access
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

        //====================================================================//
        // Title
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("post_title")
                ->Name(__("Title"))
                ->Description(__("Post") . " : " . __("Title"))
                ->MicroData("http://schema.org/Article", "name")
                ->isRequired()
                ->isLogged()
                ->isListed()
            ;

        //====================================================================//
        // Slug
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("post_name")
                ->Name(__("Slug"))
                ->Description(__("Post") . " : " . __("Permalink"))
                ->MicroData("http://schema.org/Article", "identifier")
                ->addOption("isLowerCase")
                ->isListed()
                ->isLogged()
            ;
        
        //====================================================================//
        // Contents
        $this->fieldsFactory()->Create(SPL_T_TEXT)
                ->Identifier("post_content")
                ->Name(__("Contents"))
                ->Description(__("Post") . " : " . __("Contents"))
                ->MicroData("http://schema.org/Article", "articleBody")
                ->isLogged()
            ;
        
        //====================================================================//
        // Status
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("post_status")
                ->Name(__("Status"))
                ->Description(__("Post") . " : " . __("Status"))
                ->MicroData("http://schema.org/Article", "status")
                ->AddChoices(get_post_statuses())
                ->isListed()
            ;
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
    private function getCoreFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'post_name':
            case 'post_title':
            case 'post_content':
            case 'post_status':
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
     *  @return       void
     */
    private function setCoreFields($FieldName, $Data)
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            //====================================================================//
            // Fullname Writtings
            case 'post_name':
            case 'post_title':
            case 'post_content':
            case 'post_status':
                $this->setSimple($FieldName, $Data);
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
}
