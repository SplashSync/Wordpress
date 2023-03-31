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
    protected function buildTotalsFields(): void
    {
        //====================================================================//
        // Order Total Price HT
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("total_ht")
            ->name(__("Order total")." (Tax Excl.)")
            ->microData("http://schema.org/Invoice", "totalPaymentDue")
            ->group("Totals")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Price TTC
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("total")
            ->name(__("Order total"))
            ->microData("http://schema.org/Invoice", "totalPaymentDueTaxIncluded")
            ->group("Totals")
            ->isListed()
            ->isReadOnly()
        ;

        //====================================================================//
        // PRICES INFORMATIONS FOR WMS
        //====================================================================//

        //====================================================================//
        // Order Total Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_total")
            ->name("Order Total")
            ->microData("http://schema.org/Invoice", "total")
            ->group("Totals")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Shipping
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_shipping")
            ->name("Order Shipping")
            ->microData("http://schema.org/Invoice", "totalShipping")
            ->group("Totals")
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Total Discount
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("price_discount")
            ->name("Order Discounts")
            ->microData("http://schema.org/Invoice", "totalDiscount")
            ->group("Totals")
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
                $totalHt = $this->object->get_total() - $this->object->get_total_tax();
                $this->out[$fieldName] = (double) trim((string) $totalHt);

                break;
            case 'total':
                $this->out[$fieldName] = (double) trim((string) $this->object->get_total());

                break;
            case 'price_total':
                $totalHt = $this->object->get_total() - $this->object->get_total_tax();
                $this->out[$fieldName] = self::toTotalPrice(
                    $totalHt,
                    $this->object->get_total_tax()
                );

                break;
            case 'price_shipping':
                $this->out[$fieldName] = self::toTotalPrice(
                    (double) $this->object->get_shipping_total(),
                    (double) $this->object->get_shipping_tax()
                );

                break;
            case 'price_discount':
                $this->out[$fieldName] = self::toTotalPrice(
                    (double) $this->object->get_discount_total(),
                    (double) $this->object->get_discount_tax()
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Compute Order Total Price Array
     *
     * @param float $totalTaxExcl
     * @param float $totalTax
     *
     * @return null|array
     */
    private static function toTotalPrice(float $totalTaxExcl, float $totalTax): ?array
    {
        $totalTaxIncl = $totalTaxExcl + $totalTax;
        //====================================================================//
        // Compute VAT Rate
        $vatRate = (($totalTaxExcl > 0) && ($totalTaxIncl > 0) && ($totalTaxExcl <= $totalTaxIncl))
            ? 100 * ($totalTax) / $totalTaxExcl
            : 0.0
        ;
        //====================================================================//
        // Encode Price
        return self::prices()->encode(
            null,
            $vatRate,
            $totalTaxIncl,
            get_woocommerce_currency(),             // Currency
            get_woocommerce_currency_symbol()       // Symbol
        );
    }
}
