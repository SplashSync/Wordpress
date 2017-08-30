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
 * WooCommerce Product Images Access
 */
trait ImagesTrait {
    
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
        
        foreach ($this->getImagesIds() as $Image) {
            
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
     *  @return        array
     */
    private function getImagesIds()
    {

        $Response = array();
        
        if ( $this->Product->get_image_id() ) {
            $Response[] =   array( "id" => $this->Product->get_image_id() , "cover" => True);
        }
        
        //====================================================================//
        // Detect Product Variation
        if ( $this->Product->get_parent_id() ) {
            $ImageIds    =  get_product($this->Product->get_parent_id())->get_gallery_image_ids();
        } else {
            $ImageIds    =  $this->Product->get_gallery_image_ids();
        }
        foreach ( $ImageIds as $ImageId) {
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
            
        $CurrentImages  =   $this->Product->get_gallery_image_ids();
        $NewImages      =   array();

        foreach ($Data as $ImageArray) {
           
            if ( isset($ImageArray['cover']) && isset($ImageArray['image']) && $ImageArray['cover']) {
                $this->setThumbImage($ImageArray["image"]);
                continue;
            }
            
            //====================================================================//
            // Detect Product Variation => Skipp Updates of Images Gallery
            if ( $this->Product->get_parent_id() ) {
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
        
        
        if (serialize($NewImages) !== serialize($this->Product->get_gallery_image_ids()) ) {
            $this->Product->set_gallery_image_ids($NewImages);
            $this->Product->save();
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
                $this->needUpdate();
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
            $this->needUpdate();
            return;
        } 
        // Add Image To Library
        $CreatedId = $this->insertImage($Data , $this->Object->ID);
        if ( $CreatedId ) {
            set_post_thumbnail( $this->Object->ID , $CreatedId );
            $this->needUpdate();
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
            $this->needUpdate();
            return $IdentifiedId;
        } 
        // Add Image To Library
        $CreatedId = $this->insertImage($Data , $this->Object->ID);
        if ( $CreatedId ) {
            $this->needUpdate();
            return $CreatedId;
        } 
            
        return Null;
    }
          
}
