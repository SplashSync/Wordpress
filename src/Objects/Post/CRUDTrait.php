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

namespace Splash\Local\Objects\Post;

use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Page, Post, Product CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param int|string $postId Object id
     *
     * @return false|object
     */
    public function load($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $post       =       get_post((int) $postId);
        if (is_wp_error($post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . $this->postType . " (" . $postId . ")."
            );
        }

        return $post;
    }
    
    /**
     * Create Request Object
     *
     * @return false|object
     */
    public function create()
    {
        return $this->createPost();
    }
    
    /**
     * Update Request Object
     *
     * @param array $needed Is This Update Needed
     *
     * @return false|string
     */
    public function update($needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($needed) {
            $result = wp_update_post($this->object);
            if (is_wp_error($result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $result->get_error_message()
                );
            }

            return (string) $result;
        }

        return (string) $this->object->ID;
    }
        
    /**
     * Delete requested Object
     *
     * @param int $postId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($postId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Object
        $result = wp_delete_post($postId, SPLASH_DEBUG);
        if (is_wp_error($result)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Delete " . $this->postType . ". " . $result->get_error_message()
            );
        }

        return true;
    }
    
    /**
     * Create Request Object
     *
     * @return false|object
     */
    protected function createPost()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Create Post Data
        $postData = array("post_type"  => strtolower($this->postType));
        //====================================================================//
        // Check Required Fields
        if (empty($this->in["post_title"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "post_title");
        }
        //====================================================================//
        // Multilang Mode is NOT Disabled
        if (is_array($this->in["post_title"]) || is_a($this->in["post_title"], "ArrayObject")) {
            if (empty($this->in["post_title"][get_locale()])) {
                return Splash::log()->err(
                    "ErrLocalFieldMissing",
                    __CLASS__,
                    __FUNCTION__,
                    "post_title[" . get_locale() . "]"
                );
            }
            $postData["post_title"]     =   $this->in["post_title"][get_locale()];
        } else {
            $postData["post_title"]     =   $this->in["post_title"];
        }
        //====================================================================//
        // Create Post on Db
        $postId = wp_insert_post($postData);
             
        if (is_wp_error($postId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create " . $this->postType . ". " . $postId->get_error_message()
            );
        }
        
        if (!is_int($postId)) {
            return false;
        }

        return $this->load($postId);
    }
    
    /**
     * Common Reading of a Post Meta Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getPostMeta($fieldName)
    {
        $this->out[$fieldName] = get_post_meta($this->object->ID, $fieldName, true);

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
    protected function setPostMeta($fieldName, $fieldData)
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
