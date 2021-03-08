<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2021 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Core;

use Splash\Core\SplashCore      as Splash;
use WP_Error;
use WP_Post;

/**
 * Wordpress Images Access
 */
trait ImagesTrait
{
    /**
     * Encode an Image Post to Splash Image Array
     *
     * @param int $postId
     *
     * @return array|false
     */
    protected function encodeImage($postId)
    {
        $post = get_post($postId);
        //====================================================================//
        // Image not Found
        if (is_wp_error($post) || !($post instanceof WP_Post)) {
            return false;
        }
        //====================================================================//
        // Detect Image Original Path
        $path = function_exists("wp_get_original_image_path")
            ? (string) wp_get_original_image_path($post->ID, true)
            : (string) get_attached_file($post->ID, true);
        $imageName = !empty($post->post_title) ? $post->post_title : basename($path);
        //====================================================================//
        // Insert Image in Output List
        return self::images()->Encode(
            $imageName,                 // Image Title
            basename($path),            // Image Filename
            dirname($path)."/",         // Image Path
            $post->guid                 // Image Public Url
        );
    }

    /**
     * Check if an Image Post has given Md5
     *
     * @param int|WP_Post $post WordPress Post
     * @param string      $md5  Image CheckSum
     *
     * @return bool
     */
    protected function checkImageMd5($post, $md5)
    {
        //====================================================================//
        // Safety Check
        if (empty($post)) {
            return false;
        }
        //====================================================================//
        // Load Post ID
        $postId = ($post instanceof WP_Post) ? $post->ID : $post;
        //====================================================================//
        // Compute Image Full Path
        $imagePath = function_exists("wp_get_original_image_path")
            ? (string) wp_get_original_image_path($postId, true)
            : (string) get_attached_file($postId, true);
        //====================================================================//
        // Safety Check
        if (!is_file($imagePath)) {
            return false;
        }
        //====================================================================//
        // Check Md5
        return (md5_file($imagePath) === $md5);
    }

    /**
     * Search for Image Post with given Md5
     *
     * @param mixed $md5
     *
     * @return int | null
     */
    protected function searchImageMd5($md5)
    {
        //====================================================================//
        // List Post
        $posts = get_posts(array(
            'numberposts' => -1,
            'post_type' => 'attachment'
        ));
        //====================================================================//
        // Check Post
        /** @var WP_Post $post */
        foreach ($posts as $post) {
            if ($this->checkImageMd5($post, $md5)) {
                return $post->ID;
            }
        }

        return null;
    }

    /**
     * Insert Image from Splash Server
     *
     * @param array $data
     * @param int   $parent
     *
     * @return false|int
     */
    protected function insertImage($data, $parent = 0)
    {
        //====================================================================//
        // Read File from Splash Server
        $image = Splash::file()->getFile($data["file"], $data["md5"]);
        //====================================================================//
        // File Imported => Write it Here
        if (false == $image) {
            return false;
        }
        //====================================================================//
        // Write Image to Disk
        $uploadDir = wp_upload_dir();
        Splash::file()->writeFile($uploadDir['path']."/", $data["filename"], $data["md5"], $image["raw"]);

        //====================================================================//
        // Insert Image Post
        //====================================================================//

        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype = wp_check_filetype(basename($data["filename"]), null);
        $fullpath = $uploadDir['path']."/".$data["filename"];
        // Prepare an array of post data for the attachment.
        $attachment = array(
            'guid' => $uploadDir['url'].'/'.$data["filename"],
            'post_mime_type' => $filetype['type'],
            'post_title' => preg_replace('/\.[^.]+$/', '', basename($data["filename"])),
            'post_content' => '',
            'post_status' => 'inherit'
        );

        //====================================================================//
        // Insert the attachment.
        if (!Splash::isDebugMode()) {
            set_time_limit(10);
        }
        $attachId = wp_insert_attachment($attachment, $fullpath, $parent);
        if (is_wp_error($attachId) || ($attachId instanceof WP_Error)) {
            return Splash::log()->errTrace(" Unable to Create Image. ".$attachId->get_error_message());
        }

        if (is_int($attachId)) {
            //====================================================================//
            // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
            require_once(ABSPATH.'wp-admin/includes/image.php');
            //====================================================================//
            // Generate the metadata for the attachment, and update the database record.
            $attachData = wp_generate_attachment_metadata($attachId, $fullpath);
            wp_update_attachment_metadata($attachId, $attachData);
        }

        return $attachId;
    }

    /**
     * Update Object Thumbnail Image
     *
     * @param array  $image  Splash Image Field Data
     * @param string $object Object Variable Name
     *
     * @return void
     */
    private function setThumbImage($image, $object = "object")
    {
        //====================================================================//
        // Check if Image Array is Valid
        if (empty($image) || empty($image["md5"])) {
            if (get_post_meta($this->{$object}->ID, "_thumbnail_id", true)) {
                delete_post_thumbnail($this->{$object}->ID);
                $this->needUpdate($object);
            }

            return;
        }
        //====================================================================//
        // Check if Image was modified
        $currentId = get_post_meta($this->{$object}->ID, "_thumbnail_id", true);
        if ($this->checkImageMd5($currentId, $image["md5"])) {
            return;
        }
        //====================================================================//
        // Identify Image on Library
        $identifiedId = $this->searchImageMd5($image["md5"]);
        if ($identifiedId) {
            update_post_meta($this->{$object}->ID, "_thumbnail_id", $identifiedId);
            $this->needUpdate($object);

            return;
        }
        //====================================================================//
        // Add Image To Library
        $createdId = $this->insertImage($image, $this->object->ID);
        if ($createdId) {
            set_post_thumbnail($this->{$object}->ID, $createdId);
            $this->needUpdate($object);

            return;
        }
    }
}
