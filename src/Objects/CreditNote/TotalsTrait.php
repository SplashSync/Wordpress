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

namespace Splash\Local\Objects\CreditNote;

/**
 * WooCommerce Credit Note Totals
 *
 * SIGNS
 * WooCommerce exposes the same money twice, with opposite signs:
 *   - get_total()  returns the signed value, always negative  (-118.00)
 *   - get_amount() returns the absolute value, always positive ( 118.00)
 *
 * Everything here is built on get_total(), so amounts arrive at the remote server
 * already negated, the way a credit note is expected to read. `amount` is exposed
 * separately for servers that prefer a positive figure — Dolibarr among them, since
 * it stores credit notes positive and lets its own CreditModeTrait flip the sign.
 */
trait TotalsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Totals Fields using FieldFactory
     *
     * @return void
     */
    protected function buildTotalsFields(): void
    {
        $groupName = __("Totals");

        //====================================================================//
        // Credit Note Total Tax Excluded (negative)
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("total_ht")
            ->name(__("Total")." (Tax Excl.)")
            ->microData("http://schema.org/Invoice", "totalPaymentDue")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Credit Note Total Tax Included (negative)
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("total")
            ->name(__("Total")." (Tax Incl.)")
            ->microData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->group($groupName)
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Refunded Amount (positive)
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("amount")
            ->name(__("Refund amount"))
            ->description(__("Refunded amount, as a positive value"))
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Credit Note Total Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_total")
            ->name(__("Total"))
            ->microData("http://schema.org/Invoice", "total")
            ->group($groupName)
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
    protected function getTotalsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'total_ht':
                $this->out[$fieldName] = (float) $this->object->get_total()
                    - (float) $this->object->get_total_tax();

                break;
            case 'total':
                $this->out[$fieldName] = (float) $this->object->get_total();

                break;
            case 'amount':
                $this->out[$fieldName] = (float) $this->object->get_amount();

                break;
            case 'price_total':
                $this->out[$fieldName] = self::toCreditPrice(
                    (float) $this->object->get_total() - (float) $this->object->get_total_tax(),
                    (float) $this->object->get_total_tax()
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Private Helpers
    //====================================================================//

    /**
     * Encode a Credit Note Price
     *
     * This is Order\TotalsTrait::toTotalPrice() adapted to negative amounts. The
     * Order version guards its VAT rate computation with `$totalTaxExcl > 0`,
     * which is never true on a credit note and would silently report every credit
     * note as 0% VAT. The rate is computed on absolute values instead.
     *
     * @param float $totalTaxExcl
     * @param float $totalTax
     *
     * @return null|array
     */
    private static function toCreditPrice(float $totalTaxExcl, float $totalTax): ?array
    {
        $totalTaxIncl = $totalTaxExcl + $totalTax;
        //====================================================================//
        // Compute VAT Rate on absolute values: both figures are negative here.
        $vatRate = (abs($totalTaxExcl) > 0.0)
            ? 100 * abs($totalTax) / abs($totalTaxExcl)
            : 0.0
        ;

        return self::prices()->encode(
            null,
            $vatRate,
            $totalTaxIncl,
            get_woocommerce_currency(),
            get_woocommerce_currency_symbol()
        );
    }
}
