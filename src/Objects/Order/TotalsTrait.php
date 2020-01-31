<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Order;

/**
 * WooCommerce Order Totals Data Access
 */
trait TotalsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildTotalsFields()
    {
        //====================================================================//
        // PRICES INFORMATIONS
        //====================================================================//

        //====================================================================//
        // Order Total Price HT
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("total_ht")
            ->Name(__("Order total")." (Tax Excl.)")
            ->MicroData("http://schema.org/Invoice", "totalPaymentDue")
            ->isReadOnly();

        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("total")
            ->Name(__("Order total"))
            ->MicroData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->isListed()
            ->isReadOnly();
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getTotalsFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'total_ht':
                $totalHt = $this->object->get_total() - $this->object->get_total_tax();
                $this->out[$fieldName] = (double) trim((string) $totalHt);

                break;
            case 'total':
                $this->out[$fieldName] = (double) trim($this->object->get_total());

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
