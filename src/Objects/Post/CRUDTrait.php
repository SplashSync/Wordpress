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

use Splash\Core\SplashCore      as Splash;
use WP_Error;
use WP_Post;

/**
 * WordPress Page, Post, Product CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param int|string $postId Object id
     *
     * @return null|WP_Post
     */
    public function load($postId): ?WP_Post
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        $post = get_post((int) $postId);
        if (is_wp_error($post) || !($post instanceof WP_Post)) {
            Splash::log()->errTrace("Unable to load ".$this->postType." (".$postId.").");

            return null;
        }

        return $post;
    }

    /**
     * Create Request Object
     *
     * @return null|WP_Post
     */
    public function create(): ?WP_Post
    {
        return $this->createPost();
    }

    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
     *
     * @return null|string
     */
    public function update(bool $needed): ?string
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Update User Object
        if ($needed) {
            $postId = wp_update_post($this->object);
            if (is_wp_error($postId) || ($postId instanceof WP_Error)) {
                Splash::log()->errTrace(
                    "Unable to Update ".$this->postType.". ".$postId->get_error_message()
                );

                return null;
            }
        }

        return $this->getObjectIdentifier();
    }

    /**
     * {@inheritdoc}
     */
    public function delete(string $postId): bool
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $result = wp_delete_post((int) $postId, Splash::isDebugMode());
        if (is_wp_error($result)) {
            return Splash::log()->errTrace(
                "Unable to Delete ".$this->postType.". ".$result->get_error_message()
            );
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentifier(): ?string
    {
        if (!isset($this->object->ID)) {
            return null;
        }

        return (string) $this->object->ID;
    }

    /**
     * Create Request Object
     *
     * @return null|WP_Post
     */
    protected function createPost(): ?WP_Post
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Create Post Data
        $postData = array("post_type" => strtolower($this->postType));
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["post_title"])) {
            return Splash::log()->errNull("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "post_title");
        }
        //====================================================================//
        // Multi-lang Mode is NOT Disabled
        if (is_array($this->in["post_title"])) {
            if (empty($this->in["post_title"][get_locale()])) {
                return Splash::log()->errNull(
                    "ErrLocalFieldMissing",
                    __CLASS__,
                    __FUNCTION__,
                    "post_title[".get_locale()."]"
                );
            }
            $postData["post_title"] = $this->in["post_title"][get_locale()];
        } else {
            $postData["post_title"] = $this->in["post_title"];
        }
        //====================================================================//
        // Create Post on Db
        $postId = wp_insert_post($postData);
        if (is_wp_error($postId) || ($postId instanceof WP_Error)) {
            Splash::log()->errTrace("Unable to Create ".$this->postType.". ".$postId->get_error_message());

            return null;
        }

        return $this->load((string) $postId);
    }

    /**
     * Common Reading of a Post Meta Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getPostMeta(string $fieldName): self
    {
        /** @var false|scalar $metaData */
        $metaData = get_post_meta($this->object->ID, $fieldName, true);
        $this->out[$fieldName] = $metaData;

        return $this;
    }

    /**
     * Common Writing of a Post Meta Value
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return self
     */
    protected function setPostMeta(string $fieldName, $fieldData): self
    {
        //====================================================================//
        //  Compare Field Data
        if (get_post_meta($this->object->ID, $fieldName, true) != $fieldData) {
            update_post_meta($this->object->ID, $fieldName, $fieldData);
            $this->needUpdate();
        }

        return $this;
    }
}
