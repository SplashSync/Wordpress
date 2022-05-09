<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Order;

use Splash\Local\Local;

/**
 * WooCommerce PDF Invoices Order Data Access
 */
trait WcPdfInvoiceTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildWcPdfInvoiceFields()
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooPdfInvoices()) {
            return;
        }

        //====================================================================//
        // Order Invoice Number
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("wcpdf_invoice_number")
            ->Name(__("Invoice Number"))
            ->Description(__("Wc Pdf Invoice Number"))
            ->isReadOnly()
        ;
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
    private function getWcPdfInvoiceFields($key, $fieldName)
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooPdfInvoices()) {
            return;
        }

        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'wcpdf_invoice_number':
                /** @var false|scalar $metaData */
                $metaData = get_post_meta($this->object->get_id(), "_wcpdf_invoice_number", true);
                $this->out[$fieldName] = $metaData;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }
}
