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
     * @abstract    Load Request Object
     *
     * @param       array   $Id               Object id
     *
     * @return      mixed
     */
    public function Load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $Post       =       get_post($Id);
        if (is_wp_error($Post)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to load " . $this->post_type . " (" . $Id . ").");
        }
        return $Post;
    }
    
    /**
     * @abstract    Create Request Object
     *
     * @param       array   $List         Given Object Data
     *
     * @return      object     New Object
     */
    public function Create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Create Post Data
        $PostData = array("post_type"  => strtolower($this->post_type));
        //====================================================================//
        // Check Required Fields
        if (empty($this->In["post_title"])) {
            return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "post_title");
        }
        //====================================================================//
        // Multilang Mode is NOT Disabled
        if (is_array($this->In["post_title"]) || is_a($this->In["post_title"], "ArrayObject")) {
            if (empty($this->In["post_title"][get_locale()])) {
                return Splash::log()->err("ErrLocalFieldMissing", __CLASS__, __FUNCTION__, "post_title[" . get_locale() . "]");
            }
            $PostData["post_title"]     =   $this->In["post_title"][get_locale()];
        } else {
            $PostData["post_title"]     =   $this->In["post_title"];
        }
        //====================================================================//
        // Create Post on Db
        $PostId = wp_insert_post($PostData);
             
        if (is_wp_error($PostId)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Create " . $this->post_type . ". " . $PostId->get_error_message());
        }
        
        return $this->Load($PostId);
    }
    
    /**
     * @abstract    Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      string      Object Id
     */
    public function Update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($Needed) {
            $Result = wp_update_post($this->Object);
            if (is_wp_error($Result)) {
                return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Update " . $this->post_type . ". " . $Result->get_error_message());
            }
            return (int) $Result;
        }
        return (int) $this->Object->ID;
    }
        
    /**
     * @abstract    Delete requested Object
     *
     * @param       int     $Id     Object Id.  If NULL, Object needs to be created.
     *
     * @return      bool
     */
    public function Delete($Id = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Delete Object
        $Result = wp_delete_post($Id);
        if (is_wp_error($Result)) {
            return Splash::log()->err("ErrLocalTpl", __CLASS__, __FUNCTION__, " Unable to Delete " . $this->post_type . ". " . $Result->get_error_message());
        }
        return true;
    }
    
    /**
     *  @abstract     Common Reading of a Post Meta Value
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return       self
     */
    protected function getPostMeta($FieldName)
    {
        $this->Out[$FieldName] = get_post_meta($this->Object->ID, $FieldName, true);
        return $this;
    }
    
    /**
     *  @abstract     Common Writing of a Post Meta Value
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return       self
     */
    protected function setPostMeta($FieldName, $Data)
    {
        //====================================================================//
        //  Compare Field Data
        if (get_post_meta($this->Object->ID, $FieldName, true) != $Data) {
            update_post_meta($this->Object->ID, $FieldName, $Data);
            $this->needUpdate();
        }
        return $this;
    }
}
