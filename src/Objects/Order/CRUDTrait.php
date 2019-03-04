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
use WP_Error;

/**
 * Wordpress Order CRUD Functions
 */
trait CRUDTrait
{
    /**
     * Load Request Object
     *
     * @param string $postId Object id
     *
     * @return false|WC_Order
     */
    public function load($postId)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Init Object
        $wcOrder       =       wc_get_order((int) $postId);
        if (is_wp_error($wcOrder) ||!($wcOrder instanceof WC_Order)) {
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
     * @return false|WC_Order
     */
    public function create()
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        
        $wcOrder  =   wc_create_order();
        if (is_wp_error($wcOrder) || ($wcOrder instanceof WP_Error)) {
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
        Splash::log()->trace();
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

        return $this->getObjectIdentifier();
    }
    
    /**
     * Delete requested Object
     *
     * @param string $postId Object Id.  If NULL, Object needs to be created.
     *
     * @return bool
     */
    public function delete($postId = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Delete Object
        $result = wp_delete_post((int) $postId);
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
     * {@inheritdoc}
     */
    public function getObjectIdentifier()
    {
        if (!isset($this->object->ID)) {
            return false;
        }

        return (string) $this->object->ID;
    }
}
