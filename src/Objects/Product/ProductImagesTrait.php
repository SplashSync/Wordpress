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

//use Splash\Local\Objects\Core\ImagesTrait;
use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Product Images Access
 */
trait ProductImagesTrait {
    
//    use ImagesTrait;
    
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Thumb Fields using FieldFactory
    */
    private function buildImagesFields()   {

        //====================================================================//
        // PRODUCT IMAGES
        //====================================================================//
        
        //====================================================================//
        // Product Images List
        $this->FieldsFactory()->Create(SPL_T_IMG)
                ->Identifier("image")
                ->InList("images")
                ->Name( __("Images") )
                ->Description( __("Product") . " : " . __("Images") )                
                ->Group(__("Product gallery"))
                ->MicroData("http://schema.org/Product","image");
        
        //====================================================================//
        // Product Images => Is Cover
        $this->FieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("cover")
                ->InList("images")
                ->Name( __("Featured Image") )
                ->Description( __("Product") . " : " . __("Featured Image") )                
                ->MicroData("http://schema.org/Product","isCover")
                ->Group(__("Product gallery"))
                ->NotTested(); 
        
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
    private function getImagesFields($Key,$FieldName)
    {
        if ( !in_array($FieldName , array("image@images" , "cover@images") ) ) {
            return;
        }
        
        unset($this->In[$Key]);
        
        if ( !isset($this->Out["images"]) ) {
            $this->Out["images"] = array();
        }
        
        $index = 0;
        
        foreach ($this->getImagesIds($this->Object->ID) as $Image) {
            
            if ( !isset($this->Out["images"][$index]) ) {
                $this->Out["images"][$index] = array();
            }
        
            if ($FieldName === "image@images") {
                $this->Out["images"][$index]["image"]    =   $this->encodeImage( $Image["id"] );
            }
            
            if ($FieldName === "cover@images") {
                $this->Out["images"][$index]["cover"]    =   (bool) $Image["cover"];
            }
            
            $index++;    
        }
        
    }
    
    /**
     *  @abstract     Read Product Images List
     * 
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     * 
     *  @return         none
     */
    private function getImagesIds($Post_Id)
    {

        $Response = array();
        $Product = get_product($Post_Id);
        
        if ( $Product->get_image_id() ) {
            $Response[] =   array( "id" => $Product->get_image_id() , "cover" => True);
        }
        
        foreach ( $Product->get_gallery_image_ids() as $ImageId) {
            $Response[] =   array( "id" => $ImageId , "cover" => False);
        }
            
        return $Response;
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
    private function setImagesFields($FieldName,$Data) 
    {
        if ( $FieldName !== "images" ) {
            return;
        }
        
        unset($this->In[$FieldName]);
                
        $Product        =   get_product($this->Object->ID);
        $CurrentImages  =   $Product->get_gallery_image_ids();
        $NewImages      =   array();

        foreach ($Data as $ImageArray) {
           
            if ( isset($ImageArray['cover']) && isset($ImageArray['image']) && $ImageArray['cover']) {
                $this->setThumbImage($ImageArray["image"]);
                continue;
            }
            
            if ( !isset($ImageArray['image']) ) {
                continue;
            }
            
            $NewImages[] = $this->setProductImage( $ImageArray["image"] , array_shift($CurrentImages));
            
        }
        
        if ( !empty($CurrentImages) ) {
            $this->update = True;
        }
        
        
        if (serialize($NewImages) !== serialize($Product->get_gallery_image_ids()) ) {
            $Product->set_gallery_image_ids($NewImages);
            $Product->save();
        }
            
    }
        
    /**
     *  @abstract     Update Product Thumbnail Image
     * 
     *  @param        mixed     $Data                   Field Data
     * 
     *  @return         none
     */
    private function setThumbImage($Data) 
    {
        // Check if Image Array is Valid
        if ( empty($Data) || empty($Data["md5"]) ) {
            if ( get_post_meta( $this->Object->ID, "_thumbnail_id", True ) ) {
                delete_post_thumbnail( $this->Object->ID );
                $this->update = True;
            } 
            return;
        }                 
        // Check if Image was modified
        $CurrentId = get_post_meta( $this->Object->ID, "_thumbnail_id", True );
        if ( $this->checkImageMd5($CurrentId, $Data["md5"]) ) {
            return;
        } 
        // Identify Image on Library
        $IdentifiedId = $this->searchImageMd5($Data["md5"]);
        if ( $IdentifiedId ) {
            update_post_meta( $this->Object->ID, "_thumbnail_id", $IdentifiedId );
            $this->update = True;
            return;
        } 
        // Add Image To Library
        $CreatedId = $this->insertImage($Data , $this->Object->ID);
        if ( $CreatedId ) {
            set_post_thumbnail( $this->Object->ID , $CreatedId );
            $this->update = True;
            return;
        } 
            
        return;
    }
    
    /**
     *  @abstract     Update Product Gallery Image
     * 
     *  @param        mixed     $Data                   Field Data
     * 
     *  @return         none
     */
    private function setProductImage($Data, $CurrentId) 
    {
        // Check if Image Array is Valid
        if ( empty($Data) || empty($Data["md5"]) ) {
             return Null;
        }                 
        // Check if Image was modified
        if ( $this->checkImageMd5($CurrentId, $Data["md5"]) ) {
            return $CurrentId;
        } 
        // Identify Image on Library
        $IdentifiedId = $this->searchImageMd5($Data["md5"]);
        if ( $IdentifiedId ) {
            $this->update = True;
            return $IdentifiedId;
        } 
        // Add Image To Library
        $CreatedId = $this->insertImage($Data , $this->Object->ID);
        if ( $CreatedId ) {
            $this->update = True;
            return $CreatedId;
        } 
            
        return Null;
    }
          
}
