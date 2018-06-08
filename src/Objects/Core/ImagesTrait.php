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

namespace Splash\Local\Objects\Core;

use Splash\Core\SplashCore      as Splash;

/**
 * Wordpress Images Access
 */
trait ImagesTrait
{
    
    /**
     *      @abstract       Encode an Image Post to Splash Image Array
     *      @return         array
     */
    protected function encodeImage($Post_Id)
    {
        
        $UploadsDir     = wp_upload_dir();
        $Post           = get_post($Post_Id);

        //====================================================================//
        // Image not Found
        if (is_wp_error($Post)) {
            return null;
        }
        
        $RelativePath   =   get_post_meta($Post_Id, "_wp_attached_file", true);
        $Path           =   $UploadsDir["basedir"] . "/" . dirname($RelativePath) . "/";
        $Filename       =   basename($RelativePath);
        $ImageName      =   !empty($Post->post_title) ? $Post->post_title : $Filename;
        
        //====================================================================//
        // Insert Image in Output List
        return self::Images()->Encode(
            $ImageName,                 // Image Title
            $Filename,                  // Image Filename
            $Path,                      // Image Path
            $Post->guid                 // Image Public Url
        );
    }
    
    /**
     *      @abstract       Check if an Image Post has given Md5
     *      @return         array
     */
    protected function checkImageMd5($Post, $Md5)
    {
        //====================================================================//
        // Load Post
        if (!is_object($Post)) {
            $Post   =   get_post($Post);
        }
        //====================================================================//
        // Compute Md5
        $UploadsDir     = wp_upload_dir();
        $Current        = md5_file($UploadsDir["basedir"] . "/" . get_post_meta($Post->ID, "_wp_attached_file", true));
        //====================================================================//
        // Check Md5
        return ($Current === $Md5);
    }
    
    /**
     *      @abstract       Search for Image Post with given Md5
     *      @return         int | null
     */
    protected function searchImageMd5($Md5)
    {
        
        //====================================================================//
        // List Post
        $Posts  =   get_posts(['post_type' => 'attachment' ]);
                
        //====================================================================//
        // Check Post
        foreach ($Posts as $Post) {
            if ($this->checkImageMd5($Post, $Md5)) {
                return $Post->ID;
            }
        }
        
        return null;
    }
    
    /**
     *      @abstract       Insert Image from Splash Server
     *      @return         int | null
     */
    protected function insertImage($Data, $Parent = 0)
    {
        
        //====================================================================//
        // Read File from Splash Server
        $Image    =   Splash::File()->getFile($Data["file"], $Data["md5"]);
        
        //====================================================================//
        // File Imported => Write it Here
        if ($Image == false) {
            return null;
        }
        
        //====================================================================//
        // Write Image to Disk
        $UploadsDir     = wp_upload_dir();
        Splash::File()->WriteFile($UploadsDir['path'] . "/", $Data["filename"], $Data["md5"], $Image["raw"]);

        //====================================================================//
        // Insert Image Post
        //====================================================================//
        
        // Check the type of file. We'll use this as the 'post_mime_type'.
        $filetype   = wp_check_filetype(basename($Data["filename"]), null);
        $fullpath   = $UploadsDir['path'] . "/" . $Data["filename"];
        // Prepare an array of post data for the attachment.
        $attachment = array(
                'guid'           => $UploadsDir['url'] . '/' . $Data["filename"],
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($Data["filename"])),
                'post_content'   => '',
                'post_status'    => 'inherit'
        );
        // Insert the attachment.
        $attach_id = wp_insert_attachment($attachment, $fullpath, $Parent);
        // Make sure that this file is included, as wp_generate_attachment_metadata() depends on it.
        require_once(ABSPATH . 'wp-admin/includes/image.php');
        // Generate the metadata for the attachment, and update the database record.
        $attach_data = wp_generate_attachment_metadata($attach_id, $fullpath);
        wp_update_attachment_metadata($attach_id, $attach_data);
        
        return $attach_id;
    }
}
