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

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;

/**
 * @abstract    WooCommerce Order Core Data Access
 */
trait CoreTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Core Fields using FieldFactory
    */
    private function buildCoreFields()   {

        
        
        //====================================================================//
        // Customer Object
        $this->FieldsFactory()->Create(self::Objects()->Encode( "ThirdParty" , SPL_T_ID))
                ->Identifier("_customer_id")
                ->Name(__("Customer"))
                ->isRequired();  
        if ( is_a( $this , "\Splash\Local\Objects\Invoice" ) ) {
            $this->FieldsFactory()
                    ->MicroData("http://schema.org/Invoice","customer");
        } else {
            $this->FieldsFactory()
                    ->MicroData("http://schema.org/Organization","ID");
        } 
                        
        //====================================================================//
        // Reference
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("reference")
                ->Name(__("Reference"))
                ->MicroData("http://schema.org/Order","orderNumber")       
                ->ReadOnly()
                ->IsListed();

        //====================================================================//
        // Order Date 
        $this->FieldsFactory()->Create(SPL_T_DATE)
                ->Identifier("_date_created")
                ->Name(__("Order date"))
                ->MicroData("http://schema.org/Order","orderDate")
                ->isRequired();        
        
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
    private function getCoreFields($Key,$FieldName)
    {
        
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case '_customer_id':
                if ( !$this->Object->get_customer_id() ) {
                    $this->Out[$FieldName] = Null;
                } 
                $this->Out[$FieldName] = self::Objects()->Encode( "ThirdParty" , $this->Object->get_customer_id());
                break;            
            
            case 'reference':
                $this->Out[$FieldName] = "#" . $this->Object->get_order_number();
                break;         
            
            case '_date_created':
                $this->Out[$FieldName] = $this->Object
                    ->get_date_created()
                    ->format( SPL_T_DATECAST );
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
    private function setCoreFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            case '_customer_id':
                $this->setGeneric($FieldName, self::Objects()->Id($Data));
                break;
            
            case '_date_created':
                $this->setGeneric($FieldName, $Data);
                break;            

            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
}
