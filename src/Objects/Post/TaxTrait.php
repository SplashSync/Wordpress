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
 * @abstract    Wordpress Taximony Data Access
 */
trait TaxTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build TAx Fields using FieldFactory
    */
    private function buildTaxFields()   {

        //====================================================================//
        // TAXIMONY
        //====================================================================//        
        
        //====================================================================//
        // Parent Object
        $this->FieldsFactory()->Create(self::Objects()->Encode( "Page" , SPL_T_ID))
                ->Identifier("post_parent")
                ->Name(__("Parent"))
                ->MicroData("http://schema.org/Article","mainEntity")
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
     *  @return         none
     */
    private function getTaxFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case 'post_parent':
                
                if ( !$this->Object->post_parent ) {
                    $this->Out[$FieldName] = 0;
                    break;
                }
                $this->Out[$FieldName] = self::Objects()->Encode("Page",$this->Object->post_parent);
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
    private function setTaxFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            case 'post_parent':
                $PostId =  self::Objects()->Id($Data);
                $this->setSimple($FieldName, ( get_post($PostId) ? $PostId : 0 ));
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
}
