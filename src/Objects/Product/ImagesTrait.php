<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
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
     *
     * @return void
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
     *
     * @return void
     */
    protected function buildImagesFields()
    {
        //====================================================================//
        // PRODUCT IMAGES
        //====================================================================//

        //====================================================================//
        // Product Images List
        $this->fieldsFactory()->create(SPL_T_IMG)
            ->identifier("image")
            ->inList("images")
            ->name(__("Images"))
            ->description(__("Product Images"))
            ->group(__("Product gallery"))
            ->microData("http://schema.org/Product", "image")
        ;
        //====================================================================//
        // Product Images => Image Position In List
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("position")
            ->inList("images")
            ->name(__("Position"))
            ->description(__("Image Order for this Product Variant"))
            ->microData("http://schema.org/Product", "positionImage")
            ->group(__("Product gallery"))
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Visible Image
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("visible")
            ->inList("images")
            ->name(__("Enable"))
            ->description(__("Image is visible for this Product Variant"))
            ->microData("http://schema.org/Product", "isVisibleImage")
            ->group(__("Product gallery"))
            ->isNotTested()
        ;
        //====================================================================//
        // Product Images => Is Cover
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("cover")
            ->inList("images")
            ->name(__("Featured Image"))
            ->description(__("Image is Main Product Cover Image"))
            ->microData("http://schema.org/Product", "isCover")
            ->group(__("Product gallery"))
            ->isNotTested()
        ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getImagesFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "images", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // For All Available Product Images
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
            self::lists()->insert($this->out, "images", $fieldName, $index, $value);
        }
        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string                                $fieldName Field Identifier / Name
     * @param array<array>|ArrayObject<ArrayObject> $images    Field Data
     *
     * @return void
     */
    protected function setImagesFields(string $fieldName, $images)
    {
        if ("images" !== $fieldName) {
            return;
        }

        unset($this->in[$fieldName]);
        $newImages = array();

        //====================================================================//
        // Load Product Images Array
        if (isset($this->baseProduct) && $this->isVariantsProduct()) {
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
            if (!$this->updateProductCover((array) $image)) {
                continue;
            }
            //====================================================================//
            // Variant Product Cover Image Received
            if (!$this->updateVariantThumb((array) $image)) {
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

    //====================================================================//
    // PRIVATE - Fields Reading Functions
    //====================================================================//

    /**
     * Prepare Product Images Information Array
     *
     * @return array
     */
    private function getImagesInfoArray(): array
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
        // Detect Product Common Images
        $this->loadCommonImagesInfoArray();

        return is_null($this->imgInfoCache) ? array() : $this->imgInfoCache;
    }

    /**
     * Prepare Base Product Common Images Information Array
     *
     * @return void
     */
    private function loadCoverImagesInfoArray(): void
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
     * @return void
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function loadVariantsCoverImagesInfoArray(): void
    {
        //====================================================================//
        // Add Parent Product Cover Image
        if (isset($this->baseProduct) && $this->baseProduct->get_image_id()) {
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
        $childrenIds = $this->isBaseProduct($this->product->get_parent_id());
        if (empty($childrenIds)) {
            return;
        }
        //====================================================================//
        // Walk on All Product Variants
        foreach ($childrenIds as $childrenId) {
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
     *
     * @return void
     */
    private function loadCommonImagesInfoArray()
    {
        //====================================================================//
        // Detect Variant Product
        $gallery = ($this->isVariantsProduct() && isset($this->baseProduct))
            ? $this->baseProduct->get_gallery_image_ids()
            : $this->product->get_gallery_image_ids()
        ;
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
    // PRIVATE - Fields Writing Functions
    //====================================================================//

    /**
     * Get Image Index Based on Given Position or List Index
     *
     * @param int   $index List Index
     * @param array $data  Field Data
     *
     * @return int
     */
    private function getImagePosition($index, $data): int
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
     * @return bool Continue Images Loop ?
     */
    private function updateProductCover(array $image): bool
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
     * @return bool Continue Images Loop ?
     */
    private function updateVariantThumb(array $image): bool
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
     * @param array    $fieldData Field Data
     * @param null|int $currentId
     *
     * @return null|int
     */
    private function setProductImage(array $fieldData, ?int $currentId): ?int
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
        if ($this->isVariantsProduct() && isset($this->baseProduct)) {
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
     *
     * @return void
     */
    private function saveProductImage(array $newImages)
    {
        if ($this->isVariantsProduct() && isset($this->baseProduct)) {
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
