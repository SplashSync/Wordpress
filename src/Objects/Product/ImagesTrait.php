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

use ArrayObject;

use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Images Access
 */
trait ImagesTrait
{

    /** @var array **/
    private $ImgInfoCache  = null;
    /** @var bool **/
    private $FirstVisible  = true;

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Thumb Fields using FieldFactory
    */
    private function buildImagesFields()
    {

        //====================================================================//
        // PRODUCT IMAGES
        //====================================================================//

        //====================================================================//
        // Product Images List
        $this->fieldsFactory()->Create(SPL_T_IMG)
                ->Identifier("image")
                ->InList("images")
                ->Name(__("Images"))
                ->Description(__("Product Images"))
                ->Group(__("Product gallery"))
                ->MicroData("http://schema.org/Product", "image");

        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
                ->Identifier("position")
                ->InList("images")
                ->Name(__("Position"))
                ->Description(__("Image Order for this Product Variant"))
                ->MicroData("http://schema.org/Product", "positionImage")
                ->Group(__("Product gallery"))
                ->isNotTested();

        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
                ->Identifier("visible")
                ->InList("images")
                ->Name(__("Enable"))
                ->Description(__("Image is visible for this Product Variant"))
                ->MicroData("http://schema.org/Product", "isVisibleImage")
                ->Group(__("Product gallery"))
                ->isNotTested();

        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("cover")
                ->InList("images")
                ->Name(__("Featured Image"))
                ->Description(__("Image is Main Product Cover Image"))
                ->MicroData("http://schema.org/Product", "isCover")
                ->Group(__("Product gallery"))
                ->isNotTested();
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
    private function getImagesFields($Key, $FieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $FieldId = self::lists()->InitOutput($this->Out, "images", $FieldName);
        if (!$FieldId) {
            return;
        }
        //====================================================================//
        // For All Availables Product Images
        foreach ($this->getImagesInfoArray() as $Index => $Image) {
            //====================================================================//
            // Prepare
            switch ($FieldId) {
                case "image":
                    $Value  =   $this->encodeImage($Image["id"]);
                    break;
                case "position":
                case "visible":
                case "cover":
                    $Value  =   $Image[$FieldId];
                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->Out, "images", $FieldName, $Index, $Value);
        }
        unset($this->In[$Key]);
    }

    /**
     * @abstract    Prepare Product Images Information Array
     * @return      array
     */
    private function getImagesInfoArray()
    {
        //====================================================================//
        // Detect Images Array Cache
        if (!is_null($this->ImgInfoCache)) {
            return $this->ImgInfoCache;
        } 
        //====================================================================//
        // Init Images Cache
        $this->ImgInfoCache = array();
        //====================================================================//
        // Detect Product Cover Images
        $this->loadCoverImagesInfoArray();
        //====================================================================//
        // Detect Product Comon Images
        $this->loadCommonImagesInfoArray();
        return $this->ImgInfoCache;
    }

    /**
     * @abstract    Prepare Base Product Common Images Information Array
     * @return      void
     */
    private function loadCoverImagesInfoArray()
    {
        //====================================================================//
        // Simple Product has Cover Image        
        if (!$this->isVariantsProduct()) {
            if ($this->Product->get_image_id()) {
                $this->ImgInfoCache[] =   $this->buildInfo(
                    $this->Product->get_image_id(), 
                    count($this->ImgInfoCache), 
                    true, 
                    true
                );
            }
            return;
        }        
        //====================================================================//
        // Add Parent Product Cover Image
        if ($this->BaseProduct->get_image_id()) {
            $this->ImgInfoCache[] =   $this->buildInfo(
                $this->BaseProduct->get_image_id(), 
                count($this->ImgInfoCache), 
                true, 
                false
            );
        }

        //====================================================================//
        // Variable Product Cover Images        
        //====================================================================//
        
        //====================================================================//
        // Load Ids of all Product Variants
        $Childrens = $this->isBaseProduct($this->Product->get_parent_id());
        if (empty($Childrens)) {
            return;
        }
        //====================================================================//
        // Walk on All Product Variants
        foreach ($Childrens as $ChildId) {
            //====================================================================//
            // Load Product Variant
            $Variant = wc_get_product($ChildId);
            if (empty($Variant) || !$Variant->get_image_id()) {
                continue;
            }
            $this->ImgInfoCache[] =   $this->buildInfo(
                $Variant->get_image_id(), 
                count($this->ImgInfoCache), 
                false, 
                ($ChildId == $this->Product->get_id())
            );
        }
    }    
    
    /**
     * @abstract    Prepare Base Product Common Images Information Array
     * @return      void
     */
    private function loadCommonImagesInfoArray()
    {
        //====================================================================//
        // Detect Variant Product
        $Gallery = $this->isVariantsProduct() 
                ? $this->BaseProduct->get_gallery_image_ids()
                : $this->Product->get_gallery_image_ids();
        //====================================================================//
        // Product Images to Info Array
        foreach ($Gallery as $ImageId) {
            $this->ImgInfoCache[] =   $this->buildInfo($ImageId, count($this->ImgInfoCache));
        }
    }    
    
    /**
     * @abstract    Prepare Information Array for An Image
     * @param       int     $ImageId    Image Object Id
     * @param       int     $Position   Image Position
     * @param       bool    $isCover    Image is Product Cover
     * @param       bool    $isVisible  Image is Visible for this Product Variant
     * @return      array
     */
    private function buildInfo($ImageId, $Position, $isCover = false, $isVisible = true)
    {
        return new ArrayObject(
            array(
                "id"        => $ImageId,
                "position"  => $Position,
                "cover"     => $isCover,
                "visible"   => $isVisible
            ),
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Images                   Field Data
     *
     *  @return       void
     */
    private function setImagesFields($FieldName, $Images)
    {
        if ($FieldName !== "images") {
            return;
        }

        unset($this->In[$FieldName]);
        $NewImages      =   array();

        //====================================================================//
        // Load Product Images Array
        if ($this->isVariantsProduct()) {
            $CurrentImages  =   $this->BaseProduct->get_gallery_image_ids();
        } else {
            $CurrentImages  =   $this->Product->get_gallery_image_ids();
        }
        //====================================================================//
        // Walk on Received Product Images
        $Index              = 0;
        $this->FirstVisible = true;
        foreach ($Images as $Image) {
            $Index++;
            //====================================================================//
            // Safety Check => Image Array Received
            if (!isset($Image['image'])) {
                continue;
            }
            //====================================================================//
            // Product Cover Image Received
            if (!$this->updateProductCover($Image)) {
                continue;
            }
            //====================================================================//
            // Variant Product Cover Image Received
            if (!$this->updateVariantThumb($Image)) {
                continue;
            }
            $NewImages[] = $this->setProductImage($Image["image"], array_shift($CurrentImages));
        }
        //====================================================================//
        // Save Product Images
        $this->saveProductImage($NewImages);
        //====================================================================//
        // Flush Images Infos Cache
        $this->ImgInfoCache = null;          
    }

    /**
     * @abstract    Get Image Index Based on Given Position or List Index
     * @param       int         $index      List Index
     * @param       array       $data       Field Data
     * @return      int
     */
    private function getImagePosition($index, $data)
    {
        //====================================================================//
        // Position is Given
        if (isset($data['position'])) {
            return $data['position'];
        }
        return $index;
    }

    /**
     * @abstract    Update Base Product Cover Image
     * @param       array       $Image      Field Data
     * @return      bool                    Continue Images Loop?
     */
    private function updateProductCover($Image)
    {
        //====================================================================//
        // Product Cover Image Received
        if (isset($Image['cover']) && $Image['cover']) {
            if ($this->isVariantsProduct()) {
                $this->setThumbImage($Image["image"], "BaseObject");
            } else {
                $this->setThumbImage($Image["image"], "Object");
                return false;
            }
        }
        return true;
    }
    
    /**
     * @abstract    Update Base Product Cover Image
     * @param       array       $Image      Field Data
     * @return      bool                    Continue Images Loop?
     */
    private function updateVariantThumb($Image)
    {
        //====================================================================//
        // For Variant Products Only
        if (!$this->isVariantsProduct()) {
            return true;
        }
        //====================================================================//
        // Visible ? Variant Image MUST be Visible if defined
        if (isset($Image['visible']) && (empty($Image['visible']))) {
            return false;
        }
        //====================================================================//
        // is First Visible? => is Variant Cover Image
        if (!$this->FirstVisible) {
            return true;
        }
        //====================================================================//
        // Update Variant Cover Image
        $this->setThumbImage($Image["image"], "Object");    
        //====================================================================//
        // First Visible was Found
        $this->FirstVisible = false;
        return false;
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
        //====================================================================//
        // Check if Image Array is Valid
        if (empty($Data) || empty($Data["md5"])) {
             return null;
        }
        //====================================================================//
        // Check if Image was modified
        if ($this->checkImageMd5($CurrentId, $Data["md5"])) {
            return $CurrentId;
        }
        //====================================================================//
        // Identify Image on Library
        $IdentifiedId = $this->searchImageMd5($Data["md5"]);
        if ($IdentifiedId) {
            $this->needUpdate();
            return $IdentifiedId;
        }
        //====================================================================//
        // Add Image To Library
        if ($this->isVariantsProduct()) {
            $CreatedId = $this->insertImage($Data, $this->BaseObject->ID);
        } else {
            $CreatedId = $this->insertImage($Data, $this->Object->ID);
        }
        //====================================================================//
        // New Image Created
        if ($CreatedId) {
            $this->needUpdate();
            return $CreatedId;
        }
        return null;
    }

    /**
     *  @abstract     Save Product Gallery Image
     *  @param        array     $NewImages      Product Images Gallery Array
     *  @return       void
     */
    private function saveProductImage($NewImages)
    {
        if ($this->isVariantsProduct()) {
            $Product = $this->BaseProduct;
        } else {
            $Product = $this->Product;
        }
        if (serialize($NewImages) !== serialize($Product->get_gallery_image_ids())) {
            $Product->set_gallery_image_ids($NewImages);
            $Product->save();
        }
    }
}
