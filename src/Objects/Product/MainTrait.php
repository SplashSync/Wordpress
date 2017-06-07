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

namespace Splash\Local\Objects\Product;

/**
 * @abstract    Wordpress Core Data Access
 */
trait MainTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Main Fields using FieldFactory
    */
    private function buildMainFields()   {

        //====================================================================//
        // Reference
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("_sku")
                ->Name( __("SKU") )
                ->Description( __("Product") . " : " . __("SKU") )
                ->IsListed()
                ->MicroData("http://schema.org/Product","model")
                ->isRequired();
        
        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        $GroupName  = __("Shipping");
        
        //====================================================================//
        // Weight
        $this->FieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_weight")
                ->Name( __("Weight") )
                ->Description( __("Product") . " " . __("Weight") )
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product","weight");
        
        //====================================================================//
        // Height
        $this->FieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_height")
                ->Name( __("Height") )
                ->Description( __("Product") . " " . __("Height") )
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product","height");
        
        //====================================================================//
        // Depth
        $this->FieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_length")
                ->Name( __("Length") )
                ->Description( __("Product") . " " . __("Length") )
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product","depth");
        
        //====================================================================//
        // Width
        $this->FieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_width")
                ->Name( __("Width") )
                ->Description( __("Product") . " " . __("Width") )
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product","width");
        
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
    private function getMainFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case '_sku':
            case '_weight':
            case '_length':
            case '_width':
            case '_height':
                $this->getPostMeta($FieldName);
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
    private function setMainFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            case '_sku':
            case '_weight':
            case '_length':
            case '_width':
            case '_height':
                $this->setPostMeta($FieldName,$Data);
                break;

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
}
