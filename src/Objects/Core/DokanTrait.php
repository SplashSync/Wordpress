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

use Splash\Local\Local;

trait DokanTrait
{
    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    private function buildDokanFields()
    {
        //====================================================================//
        // Check if Dokan is active
        if (!Local::hasDokan()) {
            return;
        }

        //====================================================================//
        // Dolibarr Entity ID
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("vendor_id")
            ->name("Vendor ID")
            ->microData("http://schema.org/Author", "identifier")
            ->isReadOnly()
            ->setPreferNone()
            ->isNotTested();
        //====================================================================//
        // Dolibarr Entity Code
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("vendor_code")
            ->name("Vendor Code")
            ->microData("http://schema.org/Author", "alternateName")
            ->isReadOnly()
            ->isNotTested();
        //====================================================================//
        // Dolibarr Entity Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("vendor_name")
            ->name("Entity Name")
            ->microData("http://schema.org/Author", "name")
            ->isReadOnly()
            ->setPreferNone()
            ->isNotTested();
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getDokanFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'vendor_id':
                $this->out[$fieldName] = $this->getDokanSellerId();

                break;
            case 'vendor_code':
                $seller = get_user_by("ID", $this->getDokanSellerId());
                $this->out[$fieldName] = $seller? $seller->user_login : "default";

                break;
            case 'vendor_name':
                $seller = get_user_by("ID", $this->getDokanSellerId());
                $this->out[$fieldName] = $seller?  $seller->display_name : "default";

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Safe Get Dokan Seller ID
     *
     * @return int
     */
    private function getDokanSellerId(): int
    {
        if (($this->object instanceof \WC_Order) && function_exists("dokan_get_seller_id_by_order")) {
            // @phpstan-ignore-next-line
            return dokan_get_seller_id_by_order($this->object->get_id());
        }

        if (($this->object instanceof \WP_Post)) {
            return (int) $this->object->post_author;
        }

        return 0;
    }
}
