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
     * @param       string|int      $Id               Object id
     *
     * @return      object|false
     */
    public function load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $Post       =       get_post((int) $Id);
        if (is_wp_error($Post)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . $this->postType . " (" . $Id . ")."
            );
        }
        return $Post;
    }
    
    /**
     * Create Request Object
     *
     * @return      object|false
     */
    public function create()
    {
        return $this->createPost();
    }
    
    /**
     * Create Request Object
     *
     * @return      object|false
     */
    protected function createPost()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Create Post Data
        $PostData = array("post_type"  => strtolower($this->postType));
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
            $PostData["post_title"]     =   $this->in["post_title"][get_locale()];
        } else {
            $PostData["post_title"]     =   $this->in["post_title"];
        }
        //====================================================================//
        // Create Post on Db
        $PostId = wp_insert_post($PostData);
             
        if (is_wp_error($PostId)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create " . $this->postType . ". " . $PostId->get_error_message()
            );
        }
        
        if (!is_int($PostId)) {
            return false;
        }
        return $this->load($PostId);
    }
    
    /**
     * Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      string|false
     */
    public function update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($Needed) {
            $Result = wp_update_post($this->object);
            if (is_wp_error($Result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $Result->get_error_message()
                );
            }
            return (string) $Result;
        }
        return (string) $this->object->ID;
    }
        
    /**
     * Delete requested Object
     *
     * @param       int     $Id     Object Id.  If NULL, Object needs to be created.
     *
     * @return      bool
     */
    public function delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Object
        $Result = wp_delete_post($Id, SPLASH_DEBUG);
        if (is_wp_error($Result)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Delete " . $this->postType . ". " . $Result->get_error_message()
            );
        }
        return true;
    }
    
    /**
     * Common Reading of a Post Meta Value
     *
     * @param        string    $FieldName              Field Identifier / Name
     *
     * @return       self
     */
    protected function getPostMeta($FieldName)
    {
        $this->out[$FieldName] = get_post_meta($this->object->ID, $FieldName, true);
        return $this;
    }
    
    /**
     * Common Writing of a Post Meta Value
     *
     * @param        string    $FieldName              Field Identifier / Name
     * @param        mixed     $Data                   Field Data
     *
     * @return       self
     */
    protected function setPostMeta($FieldName, $Data)
    {
        //====================================================================//
        //  Compare Field Data
        if (get_post_meta($this->object->ID, $FieldName, true) != $Data) {
            update_post_meta($this->object->ID, $FieldName, $Data);
            $this->needUpdate();
        }
        return $this;
    }
}
