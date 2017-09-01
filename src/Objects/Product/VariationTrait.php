<?php
/**
 * This file is part of SplashSync Project.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 * 
 *  @author    Splash Sync <www.splashsync.com>
 *  @copyright 2015-2017 Splash Sync
 *  @license   GNU GENERAL PUBLIC LICENSE Version 3, 29 June 2007
 * 
 **/

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Variation Data Access
 */
trait VariationTrait {
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Variation Fields using FieldFactory
    */
    private function buildVariationFields()   {
        
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//
        
        //====================================================================//
        // Product Variation Parent Link
        $this->FieldsFactory()->Create(self::Objects()->Encode( "Product" , SPL_T_ID))        
                ->Identifier("parent_id")
                ->Name( __("Parent"))
                ->Group("Meta")
                ->MicroData("http://schema.org/Product","isVariationOf")
                ->ReadOnly();
        
        //====================================================================//
        // Product Variation List - Product Link
        $this->FieldsFactory()->Create(self::Objects()->Encode( "Product" , SPL_T_ID))        
                ->Identifier("id")
                ->Name( __("Children"))
                ->InList("children")
                ->MicroData("http://schema.org/Product","Variation")
                ->ReadOnly();         
        
        //====================================================================//
        // Product Variation List - Product SKU
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)        
                ->Identifier("sku")
                ->Name( __("Name"))
                ->InList("children")
                ->MicroData("http://schema.org/Product","VariationName")
                ->ReadOnly();
        
        //====================================================================//
        // Product Variation List - Variation Attribute
        $this->FieldsFactory()->Create(SPL_T_VARCHAR)        
                ->Identifier("attribute")
                ->Name(  __("Attribute"))
                ->InList("children")
                ->MicroData("http://schema.org/Product","VariationAttribute")
                ->ReadOnly();
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
    private function getVariationFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case 'parent_id':
                if ( $this->Product->get_parent_id() ) {
                    $this->Out[$FieldName] = self::Objects()->Encode( "Product" , $this->Product->get_parent_id());
                    break;
                }
                $this->Out[$FieldName] = Null;
                break;
                
            default:
                return;
        }

        unset($this->In[$Key]);
    }
        
    /**
     *  @abstract     Read requested Field
     * 
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     * 
     *  @return         none
     */
    private function getVariationsFields($Key,$FieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $FieldId = self::Lists()->InitOutput( $this->Out, "children", $FieldName );
        if ( !$FieldId ) {
            return;
        } 
        //====================================================================//
        // READ Fields
        foreach ( $this->Product->get_children() as $Index => $Id) {
            switch ($FieldId)
            {

                case 'id':
                    self::Lists()->Insert( $this->Out, "children", $FieldId, $Index, self::Objects()->Encode( "Product" , $Id) );
                    break;

                case 'sku':
                    self::Lists()->Insert( $this->Out, "children", $FieldId, $Index, get_post_meta( $Id, "_sku", True ) );
                    break;

                case 'attribute':
                    self::Lists()->Insert( $this->Out, "children", $FieldId, $Index, implode( " | " , wc_get_product($Id)->get_attributes()) );
                    break;

                default:
                    return;
            }
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
    private function setVariationFields($FieldName,$Data) 
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName)
        {
            default:
                return;
        }
        
        unset($this->In[$FieldName]);
    }
    
}
