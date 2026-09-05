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

use WC_Order_Item;
use WC_Order_Item_Product;

/**
 * WooCommerce Credit Note Items List
 *
 * A WooCommerce refund can be line-based or amount-only, and amount-only is by far
 * the common case: on a real shop of ~450 refunds, 4 carried line items and 444 did
 * not. A remote accounting system still needs at least one line to build a document
 * from, so when the refund has no items, one is synthesised from the refund amount
 * and its reason. `is_virtual_item` tells the two apart, so a consumer can choose to
 * trust the detail or not.
 */
trait ItemsTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Items Fields using FieldFactory
     *
     * @return void
     */
    protected function buildItemsFields(): void
    {
        $groupName = __("Items");

        //====================================================================//
        // Credit Note Line Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->inList("items")
            ->name(__("Item"))
            ->group($groupName)
            ->microData("http://schema.org/partOfInvoice", "description")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly()
        ;
        //====================================================================//
        // Credit Note Line Product Identifier
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("product")
            ->inList("items")
            ->name(__("Product"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "productID")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly()
            ->isNotTested()
        ;
        //====================================================================//
        // Credit Note Line Quantity
        //
        // Negative, like every other figure on a credit note: WooCommerce stores
        // refunded quantities as negative on the refund's line items.
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity")
            ->inList("items")
            ->name(__("Quantity"))
            ->group($groupName)
            ->microData("http://schema.org/QuantitativeValue", "value")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly()
        ;
        //====================================================================//
        // Credit Note Line Subtotal
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("subtotal")
            ->inList("items")
            ->name(__("Subtotal"))
            ->group($groupName)
            ->microData("http://schema.org/PriceSpecification", "price")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly()
        ;
        //====================================================================//
        // Line is Synthesised, not read from WooCommerce
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("is_virtual_item")
            ->inList("items")
            ->name(__("Amount-only refund"))
            ->description(__("Line was rebuilt from the refund amount, not itemised in WooCommerce"))
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
    protected function getItemsFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "items", $fieldName);
        if (!$fieldId) {
            return;
        }
        //====================================================================//
        // Walk on Credit Note Lines
        foreach ($this->getCreditNoteLines() as $index => $line) {
            self::lists()->insert($this->out, "items", $fieldId, $index, $line[$fieldId] ?? null);
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Private Helpers
    //====================================================================//

    /**
     * Build the list of Credit Note Lines
     *
     * @return array<int, array<string, null|array|bool|float|int|string>>
     */
    private function getCreditNoteLines(): array
    {
        $lines = array();
        //====================================================================//
        // Read the refund's own line items, when it has any.
        /** @var WC_Order_Item $item */
        foreach ($this->object->get_items() as $item) {
            $lines[] = $this->toCreditNoteLine($item);
        }
        if (!empty($lines)) {
            return $lines;
        }
        //====================================================================//
        // Amount-only refund: rebuild a single line so the remote server has
        // something to write the credit note against.
        return array($this->toVirtualLine());
    }

    /**
     * Convert a WooCommerce Refund Item to a Credit Note Line
     *
     * @param WC_Order_Item $item
     *
     * @return array<string, null|array|bool|float|int|string>
     */
    private function toCreditNoteLine(WC_Order_Item $item): array
    {
        $productId = null;
        $quantity = 0;
        if ($item instanceof WC_Order_Item_Product) {
            $wcProductId = $item->get_variation_id() ?: $item->get_product_id();
            $productId = $wcProductId
                ? self::objects()->encode("Product", (string) $wcProductId)
                : null;
            $quantity = (int) $item->get_quantity();
        }

        $subtotal = (float) $item->get_total();
        $subtotalTax = (float) $item->get_total_tax();

        return array(
            "name" => (string) $item->get_name(),
            "product" => $productId,
            "quantity" => $quantity,
            "subtotal" => self::toCreditPrice($subtotal, $subtotalTax),
            "is_virtual_item" => false,
        );
    }

    /**
     * Build the single line that stands for an amount-only refund
     *
     * @return array<string, null|array|bool|float|int|string>
     */
    private function toVirtualLine(): array
    {
        //====================================================================//
        // The reason is what a human typed to explain the refund, so it makes the
        // best line description. Fall back to a neutral label when it is empty.
        $reason = trim((string) $this->object->get_reason());
        $name = $reason ?: __("Refund");

        $totalTax = (float) $this->object->get_total_tax();
        $totalHt = (float) $this->object->get_total() - $totalTax;

        return array(
            "name" => $name,
            "product" => null,
            "quantity" => -1,
            "subtotal" => self::toCreditPrice($totalHt, $totalTax),
            "is_virtual_item" => true,
        );
    }
}
