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

use Splash\Local\Objects\Core\ImagesTrait;

/**
 * Wordpress Thumb Image Access
 */
trait ThumbTrait {
    
    use ImagesTrait;
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Thumb Fields using FieldFactory
    */
    private function buildThumbFields()   {

        //====================================================================//
        // Thumbnail Image
        $this->FieldsFactory()->Create(SPL_T_IMG)
                ->Identifier("_thumbnail_id")
                ->Name( __("Featured Image") )
                ->MicroData("http://schema.org/Article","image")
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
    private function getThumbFields($Key,$FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName)
        {
            case '_thumbnail_id':
                
                if ( !has_post_thumbnail( $this->Object->ID ) ) {
                    $this->Out[$FieldName] = Null;
                    break;
                }
                
                $Thumbnail_Id = get_post_meta( $this->Object->ID, $FieldName, True );
                if ( empty($Thumbnail_Id) ) {
                    $this->Out[$FieldName] = Null;
                    break;
                }
                
                $this->Out[$FieldName] = $this->encodeImage($Thumbnail_Id);
                        
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
    private function setThumbFields($FieldName,$Data) 
    {
        if ( $FieldName !== '_thumbnail_id') {
            return;
        }
        unset($this->In[$FieldName]);
        
        // Check if Image Array is Valid
        if ( empty($Data) || empty($Data["md5"]) ) {
            if ( get_post_meta( $this->Object->ID, $FieldName, True ) ) {
                delete_post_thumbnail( $this->Object->ID );
                $this->needUpdate();
            } 
            return;
        }                 
        // Check if Image was modified
        $CurrentId = get_post_meta( $this->Object->ID, $FieldName, True );
        if ( $this->checkImageMd5($CurrentId, $Data["md5"]) ) {
            return;
        } 
        // Identify Image on Library
        $IdentifiedId = $this->searchImageMd5($Data["md5"]);
        if ( $IdentifiedId ) {
            $this->setPostMeta($FieldName,$IdentifiedId);
            return;
        } 
        // Add Image To Library
        $CreatedId = $this->insertImage($Data , $this->Object->ID);
        if ( $CreatedId ) {
            set_post_thumbnail( $this->Object->ID , $CreatedId );
            $this->needUpdate();
            return;
        } 
                
    }
    
  
}
