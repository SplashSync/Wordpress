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

namespace Splash\Local\Objects\Post;

use Splash\Local\Objects\Core\ImagesTrait;

/**
 * WordPress Thumb Image Access
 */
trait ThumbTrait
{
    use ImagesTrait;

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Thumb Fields using FieldFactory
     *
     * @return void
     */
    protected function buildThumbFields(): void
    {
        //====================================================================//
        // Thumbnail Image
        $this->fieldsFactory()->create(SPL_T_IMG)
            ->identifier("_thumbnail_id")
            ->name(__("Featured Image"))
            ->microData("http://schema.org/Article", "image")
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
    protected function getThumbFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_thumbnail_id':
                if (!has_post_thumbnail($this->object->ID)) {
                    $this->out[$fieldName] = null;

                    break;
                }

                /** @var false|scalar $thumbId */
                $thumbId = get_post_meta($this->object->ID, $fieldName, true);
                if (empty($thumbId)) {
                    $this->out[$fieldName] = null;

                    break;
                }

                $this->out[$fieldName] = $this->encodeImage((int) $thumbId);

                break;
            default:
                return;
        }
        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    protected function setThumbFields(string $fieldName, $fieldData): void
    {
        if ('_thumbnail_id' !== $fieldName) {
            return;
        }
        unset($this->in[$fieldName]);
        //====================================================================//
        // Check if Image Array is Valid
        if (!is_array($fieldData) || empty($fieldData["md5"])) {
            if (get_post_meta($this->object->ID, $fieldName, true)) {
                delete_post_thumbnail($this->object->ID);
                $this->needUpdate();
            }

            return;
        }
        //====================================================================//
        // Check if Image was modified
        /** @var false|scalar $currentId */
        $currentId = get_post_meta($this->object->ID, $fieldName, true);
        if ($currentId && $this->checkImageMd5((int) $currentId, $fieldData["md5"])) {
            return;
        }
        //====================================================================//
        // Identify Image on Library
        $identifiedId = $this->searchImageMd5($fieldData["md5"]);
        if ($identifiedId) {
            $this->setPostMeta($fieldName, $identifiedId);

            return;
        }
        //====================================================================//
        // Add Image To Library
        $createdId = $this->insertImage($fieldData, $this->object->ID);
        if ($createdId) {
            set_post_thumbnail($this->object->ID, $createdId);
            $this->needUpdate();
        }
    }
}
