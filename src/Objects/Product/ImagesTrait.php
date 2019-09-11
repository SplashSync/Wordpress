<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use ArrayObject;
use Splash\Core\SplashCore      as Splash;

/**
 * WooCommerce Product Images Access
 */
trait ImagesTrait
{
    /** @var null|array */
    private $imgInfoCache;

    /** @var bool */
    private $firstVisible = true;

    /**
     * Flush Product Images Information Cache
     */
    protected function flushImagesInfoArray()
    {
        $this->imgInfoCache = null;
    }

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Thumb Fields using FieldFactory
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
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     */
    private function getImagesFields($key, $fieldName)
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "images", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // For All Availables Product Images
        foreach ($this->getImagesInfoArray() as $index => $image) {
            //====================================================================//
            // Prepare
            switch ($fieldId) {
                case "image":
                    $value = $this->encodeImage($image["id"]);

                    break;
                case "position":
                case "visible":
                case "cover":
                    $value = $image[$fieldId];

                    break;
                default:
                    return;
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "images", $fieldName, $index, $value);
        }
        unset($this->in[$key]);
    }

    /**
     * Prepare Product Images Information Array
     *
     * @return array
     */
    private function getImagesInfoArray()
    {
        //====================================================================//
        // Detect Images Array Cache
        if (!is_null($this->imgInfoCache)) {
            return $this->imgInfoCache;
        }
        //====================================================================//
        // Init Images Cache
        $this->imgInfoCache = array();
        //====================================================================//
        // Detect Product Cover Images
        $this->loadCoverImagesInfoArray();
        //====================================================================//
        // Detect Product Comon Images
        $this->loadCommonImagesInfoArray();

        return $this->imgInfoCache;
    }

    /**
     * Prepare Base Product Common Images Information Array
     */
    private function loadCoverImagesInfoArray()
    {
        //====================================================================//
        // Simple Product has Cover Image
        if (!$this->isVariantsProduct()) {
            if ($this->product->get_image_id()) {
                $this->imgInfoCache[] = $this->buildInfo(
                    $this->product->get_image_id(),
                    is_null($this->imgInfoCache) ? 0 : count($this->imgInfoCache),
                    true,
                    true
                );
            }

            return;
        }

        $this->loadVariantsCoverImagesInfoArray();
    }

    /**
     * Prepare Variable Product Common Images Information Array
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function loadVariantsCoverImagesInfoArray()
    {
        //====================================================================//
        // Add Parent Product Cover Image
        if ($this->baseProduct->get_image_id()) {
            $this->imgInfoCache[] = $this->buildInfo(
                $this->baseProduct->get_image_id(),
                is_null($this->imgInfoCache) ? 0 : count($this->imgInfoCache),
                true,
                false
            );
        }

        //====================================================================//
        // Variable Product Cover Images
        //====================================================================//

        //====================================================================//
        // Load Ids of all Product Variants
        $childrens = $this->isBaseProduct($this->product->get_parent_id());
        if (empty($childrens)) {
            return;
        }
        //====================================================================//
        // Walk on All Product Variants
        foreach ($childrens as $childrenId) {
            //====================================================================//
            // SKIP Others Variant When in PhpUnit/Travis Mode
            if (!empty(Splash::input('SPLASH_TRAVIS')) && ($childrenId != $this->object->ID)) {
                continue;
            }
            //====================================================================//
            // Load Product Variant
            $wcProduct = wc_get_product($childrenId);
            if (empty($wcProduct) || !$wcProduct->get_image_id()) {
                continue;
            }
            $this->imgInfoCache[] = $this->buildInfo(
                $wcProduct->get_image_id(),
                is_null($this->imgInfoCache) ? 0 : count($this->imgInfoCache),
                false,
                ($childrenId == $this->product->get_id())
            );
        }
    }

    /**
     * Prepare Base Product Common Images Information Array
     */
    private function loadCommonImagesInfoArray()
    {
        //====================================================================//
        // Detect Variant Product
        $gallery = $this->isVariantsProduct()
                ? $this->baseProduct->get_gallery_image_ids()
                : $this->product->get_gallery_image_ids();
        //====================================================================//
        // Product Images to Info Array
        foreach ($gallery as $imageId) {
            $this->imgInfoCache[] = $this->buildInfo(
                $imageId,
                is_null($this->imgInfoCache) ? 0 : count($this->imgInfoCache)
            );
        }
    }

    /**
     * Prepare Information Array for An Image
     *
     * @param int|string $imageId   Image Object Id
     * @param int        $position  Image Position
     * @param bool       $isCover   Image is Product Cover
     * @param bool       $isVisible Image is Visible for this Product Variant
     *
     * @return ArrayObject
     */
    private function buildInfo($imageId, $position, $isCover = false, $isVisible = true)
    {
        return new ArrayObject(
            array(
                "id" => $imageId,
                "position" => $position,
                "cover" => $isCover,
                "visible" => $isVisible
            ),
            ArrayObject::ARRAY_AS_PROPS
        );
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $images    Field Data
     */
    private function setImagesFields($fieldName, $images)
    {
        if ("images" !== $fieldName) {
            return;
        }

        unset($this->in[$fieldName]);
        $newImages = array();

        //====================================================================//
        // Load Product Images Array
        if ($this->isVariantsProduct()) {
            $currentImages = $this->baseProduct->get_gallery_image_ids();
        } else {
            $currentImages = $this->product->get_gallery_image_ids();
        }
        //====================================================================//
        // Walk on Received Product Images
        $index = 0;
        $this->firstVisible = true;
        foreach ($images as $image) {
            $index++;
            //====================================================================//
            // Safety Check => Image Array Received
            if (!isset($image['image'])) {
                continue;
            }
            //====================================================================//
            // Product Cover Image Received
            if (!$this->updateProductCover($image)) {
                continue;
            }
            //====================================================================//
            // Variant Product Cover Image Received
            if (!$this->updateVariantThumb($image)) {
                continue;
            }
            $newImages[] = $this->setProductImage($image["image"], array_shift($currentImages));
        }
        //====================================================================//
        // Save Product Images
        $this->saveProductImage($newImages);
        //====================================================================//
        // Flush Images Infos Cache
        $this->imgInfoCache = null;
    }

    /**
     * Get Image Index Based on Given Position or List Index
     *
     * @param int   $index List Index
     * @param array $data  Field Data
     *
     * @return int
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
     * Update Base Product Cover Image
     *
     * @param array $image Field Data
     *
     * @return bool Continue Images Loop?
     */
    private function updateProductCover($image)
    {
        //====================================================================//
        // Product Cover Image Received
        if (isset($image['cover']) && $image['cover']) {
            if ($this->isVariantsProduct()) {
                $this->setThumbImage($image["image"], "baseObject");
            } else {
                $this->setThumbImage($image["image"]);

                return false;
            }
        }

        return true;
    }

    /**
     * Update Base Product Cover Image
     *
     * @param array $image Field Data
     *
     * @return bool Continue Images Loop?
     */
    private function updateVariantThumb($image)
    {
        //====================================================================//
        // For Variant Products Only
        if (!$this->isVariantsProduct()) {
            return true;
        }
        //====================================================================//
        // Visible ? Variant Image MUST be Visible if defined
        if (isset($image['visible']) && (empty($image['visible']))) {
            return false;
        }
        //====================================================================//
        // is First Visible? => is Variant Cover Image
        if (!$this->firstVisible) {
            return true;
        }
        //====================================================================//
        // Update Variant Cover Image
        $this->setThumbImage($image["image"]);
        //====================================================================//
        // First Visible was Found
        $this->firstVisible = false;

        return false;
    }

    /**
     * Update Product Gallery Image
     *
     * @param mixed $fieldData Field Data
     * @param mixed $currentId
     *
     * @return null|int
     */
    private function setProductImage($fieldData, $currentId)
    {
        //====================================================================//
        // Check if Image Array is Valid
        if (empty($fieldData) || empty($fieldData["md5"])) {
            return null;
        }
        //====================================================================//
        // Check if Image was modified
        if ($this->checkImageMd5($currentId, $fieldData["md5"])) {
            return $currentId;
        }
        //====================================================================//
        // Identify Image on Library
        $identifiedId = $this->searchImageMd5($fieldData["md5"]);
        if ($identifiedId) {
            $this->needUpdate();

            return $identifiedId;
        }
        //====================================================================//
        // Add Image To Library
        if ($this->isVariantsProduct()) {
            $createdId = $this->insertImage($fieldData, $this->baseObject->ID);
        } else {
            $createdId = $this->insertImage($fieldData, $this->object->ID);
        }
        //====================================================================//
        // New Image Created
        if ($createdId) {
            $this->needUpdate();

            return $createdId;
        }

        return null;
    }

    /**
     * Save Product Gallery Image
     *
     * @param array $newImages Product Images Gallery Array
     */
    private function saveProductImage($newImages)
    {
        if ($this->isVariantsProduct()) {
            $product = $this->baseProduct;
        } else {
            $product = $this->product;
        }
        if (serialize($newImages) !== serialize($product->get_gallery_image_ids())) {
            $product->set_gallery_image_ids($newImages);
            $product->save();
        }
    }
}
