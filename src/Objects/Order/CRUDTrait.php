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

namespace Splash\Local\Objects\Order;

use Splash\Core\SplashCore      as Splash;
use WC_Order;

/**
 * Wordpress Order CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param int|string $postId Object id
     *
     * @return mixed
     */
    public function load($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        //====================================================================//
        // Init Object
        $wcOrder       =       wc_get_order((int)$postId);
        if (is_wp_error($wcOrder)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to load " . $this->postType . " (" . $postId . ")."
            );
        }
        
        return $wcOrder;
    }
    
    /**
     * Create Request Object
     *
     * @return bool|WC_Order
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace(__CLASS__, __FUNCTION__);
        
        $wcOrder  =   wc_create_order();
        if (is_wp_error($wcOrder)) {
            return Splash::log()->err(
                "ErrLocalTpl",
                __CLASS__,
                __FUNCTION__,
                " Unable to Create " . $this->postType . ". " . $wcOrder->get_error_message()
            );
        }
        
        return $wcOrder;
    }
    
    /**
     * Update Request Object
     *
     * @param bool $needed Is This Update Needed
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
            // Update Totals
            $this->object->update_taxes();
            $this->object->calculate_totals(false);
            // Save Order
            $result = $this->object->save();
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
        $result = wp_delete_post($postId);
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
}
