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

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;

use WC_Order;

/**
 * @abstract    Wordpress Order CRUD Functions
 */
trait CRUDTrait
{
    
    /**
     * @abstract    Load Request Object
     *
     * @param       string|int      $Id               Object id
     *
     * @return      mixed
     */
    public function load($Id)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $Post       =       wc_get_order((int)$Id);
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
     * @abstract    Create Request Object
     * @return      bool|WC_Order
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        $Order  =   wc_create_order();
        if (is_wp_error($Order)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create " . $this->postType . ". " . $Order->get_error_message()
            );
        }
        
        return $Order;
    }
    
    /**
     * @abstract    Update Request Object
     *
     * @param       array   $Needed         Is This Update Needed
     *
     * @return      int|false
     */
    public function update($Needed)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Update User Object
        if ($Needed) {
            // Update Totals
            $this->object->update_taxes();
            $this->object->calculate_totals(false);
            // Save Order
            $Result = $this->object->save();
            if (is_wp_error($Result)) {
                return Splash::log()->err(
                    "ErrLocalTpl",
                    __CLASS__,
                    __FUNCTION__,
                    " Unable to Update " . $this->postType . ". " . $Result->get_error_message()
                );
            }
            return (int) $Result;
        }
        return (int) $this->object->ID;
    }
    
    /**
     * @abstract    Delete requested Object
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
        $Result = wp_delete_post($Id);
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
}
