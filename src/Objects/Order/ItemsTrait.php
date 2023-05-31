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

use Splash\Client\Splash;
use stdClass;
use WC_Meta_Data;
use WC_Order_Item;
use WC_Order_Item_Fee;
use WC_Order_Item_Product;
use WC_Order_Item_Shipping;
use WC_Product;
use WC_Tax;

/**
 * WooCommerce Order Items Data Access
 */
trait ItemsTrait
{
    /**
     * @var null|WC_Order_Item|WC_Order_Item_Product
     */
    private ?WC_Order_Item $item;

    /**
     * @var array<WC_Order_Item_Fee|WC_Order_Item_Product|WC_Order_Item_Shipping>
     */
    private array $items;

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildItemsFields(): void
    {
        $groupName = __("Items");
        $isReadOnly = !Splash::isTravisMode();

        //====================================================================//
        // Order Line Description
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("name")
            ->inList("items")
            ->name(__("Item"))
            ->group($groupName)
            ->microData("http://schema.org/partOfInvoice", "description")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly($isReadOnly)
        ;
        //====================================================================//
        // Order Line Product Identifier
        $this->fieldsFactory()->create((string) self::objects()->encode("Product", SPL_T_ID))
            ->identifier("product")
            ->inList("items")
            ->name(__("Product"))
            ->group($groupName)
            ->microData("http://schema.org/Product", "productID")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly($isReadOnly)
            ->isNotTested()
        ;
        //====================================================================//
        // Order Line Product SKU
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("sku")
            ->inList("items")
            ->name(__("SKU"))
            ->microData("http://schema.org/Product", "sku")
            ->group($groupName)
            ->isReadOnly()
        ;
        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->create(SPL_T_INT)
            ->identifier("quantity")
            ->inList("items")
            ->name(__("Quantity"))
            ->group($groupName)
            ->microData("http://schema.org/QuantitativeValue", "value")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly($isReadOnly)
        ;
        //====================================================================//
        // Order Line Discount
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("discount")
            ->inList("items")
            ->name(__("Percentage discount"))
            ->group($groupName)
            ->microData("http://schema.org/Order", "discount")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly($isReadOnly)
        ;
        //====================================================================//
        // Order Line Unit Price
        $this->fieldsFactory()->create(SPL_T_PRICE)
            ->identifier("subtotal")
            ->inList("items")
            ->name(__("Price"))
            ->group($groupName)
            ->microData("http://schema.org/PriceSpecification", "price")
            ->association("name@items", "quantity@items", "subtotal@items")
            ->isReadOnly($isReadOnly)
        ;
        //====================================================================//
        // Order Line Tax Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("tax_name")
            ->inList("items")
            ->name(__("Tax name"))
            ->microData("http://schema.org/PriceSpecification", "valueAddedTaxName")
            ->group($groupName)
            ->association("name@items", "quantity@items", "subtotal@items")
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
        // Check if List field & Init List Array
        $fieldId = self::lists()->initOutput($this->out, "items", $fieldName);
        if (!$fieldId) {
            return;
        }

        foreach ($this->loadAllItems() as $index => $item) {
            if ($item instanceof WC_Order_Item_Product) {
                $itemData = $this->getProductItemData($item, $fieldId);
            } else {
                $itemData = $this->getItemData($item, $fieldId);
            }
            //====================================================================//
            // Insert Data in List
            self::lists()->Insert($this->out, "items", $fieldName, $index, $itemData);
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string     $fieldName Field Identifier / Name
     * @param null|array $fieldData Field Data
     *
     * @return void
     */
    protected function setItemsFields(string $fieldName, ?array $fieldData): void
    {
        //====================================================================//
        // Check if List field
        if ("items" != $fieldName) {
            return;
        }
        $fieldData = $fieldData ?? array();
        //====================================================================//
        // Load Initial Version
        $this->loadAllItems();
        //====================================================================//
        // Walk on Current Order Items
        foreach ($fieldData as $itemData) {
            $this->item = array_shift($this->items);
            //====================================================================//
            // Create Item If Needed
            if (!$this->item) {
                $this->item = new WC_Order_Item_Product();
                $this->object->add_item($this->item);
            }
            //====================================================================//
            // Update Item
            if ($this->item instanceof WC_Order_Item_Product) {
                $this->setProductItem($itemData);
            } else {
                $this->setItem($itemData);
            }
        }
        //====================================================================//
        // Remove Extra Order Items
        foreach ($this->items as $item) {
            $this->object->remove_item($item->get_id());
        }

        unset($this->in["items"]);
    }

    //====================================================================//
    // Private Fields Reading Functions
    //====================================================================//

    /**
     * Read Order Item Field
     *
     * @param WC_Order_Item_Product $item
     * @param string                $fieldId
     *
     * @return null|array|double|int|string
     *
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function getProductItemData(WC_Order_Item_Product $item, string $fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            case 'name':
                return  $this->getItemName($item);
            case 'sku':
                $wcProduct = $item->get_product();

                return  ($wcProduct instanceof WC_Product) ? $wcProduct->get_sku() : null;
            case 'quantity':
                return  $item->get_quantity();
            case 'price':
                return  $this->encodePrice($item->get_total(), $item->get_total_tax(), $item->get_quantity());
            case 'tax_name':
                return  $this->encodeTaxName($item);
            case 'discount':
                // Compute Discount (Percent of Total to SubTotal)
                if ($item->get_subtotal()) {
                    $discount = 100
                        * ((double) $item->get_subtotal() - (double) $item->get_total())
                        / (double) $item->get_subtotal()
                    ;
                } else {
                    $discount = 0.0;
                }

                return  round($discount, 4);
            case 'subtotal':
                return  $this->encodePrice($item->get_subtotal(), $item->get_subtotal_tax(), $item->get_quantity());
            case 'product':
                return  $this->encodeProductId($item);
        }

        return null;
    }

    /**
     * Read Order Item Field
     *
     * @param WC_Order_Item_Fee|WC_Order_Item_Shipping $item
     * @param string                                   $fieldId
     *
     * @return null|array|double|int|string
     */
    private function getItemData(WC_Order_Item $item, string $fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            case 'name':
                return  $this->getItemName($item);
            case 'quantity':
                return   1;
            case 'price':
            case 'subtotal':
                return $this->encodePrice($item->get_total(), $item->get_total_tax(), 1);
            case 'tax_name':
                return $this->encodeTaxName($item);
            case 'discount':
                // Compute Discount (Percent of Total to SubTotal)
                return 0.0;
            case 'sku':
            case 'product':
                return null;
        }

        return null;
    }

    //====================================================================//
    // Private Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Product Item Fields
     *
     * @param array $itemData Field Data
     *
     * @return void
     */
    private function setProductItem(array $itemData): void
    {
        //====================================================================//
        // Safety Check
        if (!$this->item instanceof WC_Order_Item_Product) {
            return;
        }
        //====================================================================//
        // Update Quantity
        if (isset($itemData["quantity"])) {
            $this->setGeneric("_quantity", $itemData["quantity"], "item");
        }
        //====================================================================//
        // Update Name
        if (isset($itemData["name"])) {
            $this->setGeneric("_name", $itemData["name"], "item");
        }
        //====================================================================//
        // Update Product Id
        if (isset($itemData["product"])) {
            $productId = self::objects()->id($itemData["product"]);
            $this->setGeneric("_product_id", $productId, "item");
        }
        //====================================================================//
        // Update Unit Price
        if (isset($itemData["subtotal"])) {
            // Compute Expected Subtotal
            $subtotal = $this->item->get_quantity() * self::prices()->taxExcluded($itemData["subtotal"]);
            // Compute Expected Subtotal Tax Incl.
            $subtotalTax = $this->item->get_quantity() * self::prices()->taxAmount($itemData["subtotal"]);
        } else {
            $subtotal = $this->item->get_subtotal();
            $subtotalTax = $this->item->get_subtotal_tax();
        }
        //====================================================================//
        // Update Total Line Price
        // There is A Discount Percent
        if (isset($itemData["discount"])) {
            // Compute Expected Total
            $total = (float) $subtotal * (1 - $itemData["discount"] / 100);
            // Compute Expected Total Tax Incl.
            $totalTax = (float) $subtotalTax * (1 - $itemData["discount"] / 100);
            // There is NO Discount
        } else {
            $total = $subtotal;
            $totalTax = $subtotalTax;
        }
        //====================================================================//
        // Update Item Taxes Array
        if (($totalTax != $this->item->get_total_tax()) || ($subtotalTax != $this->item->get_subtotal_tax())) {
            $this->setProductTaxArray((float) $totalTax, (float)  $subtotalTax);
        }
        //====================================================================//
        // Update Item Totals
        $this->setGeneric("_total", $total, "item");
        $this->setGeneric("_subtotal", $subtotal, "item");
    }

    /**
     * Write Given Item Fields
     *
     * @param array $itemData Field Data
     *
     * @return void
     */
    private function setItem(array $itemData)
    {
        //====================================================================//
        // Safety Check
        if ((!$this->item instanceof WC_Order_Item_Shipping) && (!$this->item instanceof WC_Order_Item_Fee)) {
            return;
        }
        //====================================================================//
        // Update Name
        if (isset($itemData["name"])) {
            $this->setGeneric("_name", $itemData["name"], "item");
        }
        //====================================================================//
        // Update Quantity
        $qty = $itemData["quantity"] ?? 1;
        //====================================================================//
        // Update Unit Price
        if (isset($itemData["subtotal"])) {
            // Compute Expected Total
            $total = $qty * self::prices()->taxExcluded($itemData["subtotal"]);
            // Compute Expected Total Tax Incl.
            $totalTax = $qty * self::prices()->taxAmount($itemData["subtotal"]);
            // There is NO Discount
        } else {
            $total = $this->item->get_total();
            $totalTax = $this->item->get_total_tax();
        }
        //====================================================================//
        // Update Item Taxes
        if ($totalTax != $this->item->get_total_tax()) {
            $this->setItemTaxArray('total', (float) $totalTax);
        }
        //====================================================================//
        // Update Item Totals
        $this->setGeneric("_total", $total, "item");
    }

    /**
     * Write Given Tax Amount to Tax Array Row
     *
     * @param string $row    Tax Row ID
     * @param float  $amount Tax Amount
     *
     * @return void
     */
    private function setItemTaxArray(string $row, float $amount): void
    {
        if ((!$this->item instanceof WC_Order_Item_Shipping) && (!$this->item instanceof WC_Order_Item_Fee)) {
            return;
        }
        $taxes = $this->item->get_taxes();
        if (empty($taxes[$row])) {
            $taxes[$row] = array( 0 => $amount );
        } else {
            foreach ($taxes[$row] as &$value) {
                $value = $amount;
                $amount = 0;
            }
        }

        try {
            $this->item->set_taxes($taxes);
            $this->needUpdate();
        } catch (\Throwable $ex) {
            Splash::log()->report($ex);
        }
    }

    /**
     * Write Given Tax Amount to Tax Array Row
     *
     * @param float $total    Product Line Total Price
     * @param float $subtotal Product line Subtotal
     *
     * @return void
     */
    private function setProductTaxArray(float $total, float $subtotal): void
    {
        if (!$this->item instanceof WC_Order_Item_Product) {
            return;
        }
        $taxes = $this->item->get_taxes();
        if (empty($taxes['total'])) {
            $taxes['total'] = array( 0 => $total );
            $taxes['subtotal'] = array( 0 => $subtotal );
        } else {
            foreach ($taxes['total'] as &$value) {
                $value = $total;
                $total = 0;
            }
            foreach ($taxes['subtotal'] as &$value) {
                $value = $subtotal;
            }
        }
        $this->item->set_taxes($taxes);
        $this->needUpdate();
    }

    /**
     * Load All Order Items
     *
     * @return array<WC_Order_Item_Fee|WC_Order_Item_Product|WC_Order_Item_Shipping>
     */
    private function loadAllItems(): array
    {
        /** @var array<WC_Order_Item_Fee|WC_Order_Item_Product|WC_Order_Item_Shipping> $items */
        $items = array_merge(
            $this->object->get_items(),
            $this->object->get_items("shipping"),
            $this->object->get_items("fee")
        );

        return $this->items = $items;
    }

    /**
     * Detect Product ID for Order Item
     *
     * @param WC_Order_Item_Product $item
     *
     * @return null|string
     */
    private function encodeProductId(WC_Order_Item_Product $item): ?string
    {
        if (! $item->get_product_id()) {
            return null;
        }
        $productId = ($item->get_variation_id())
            ? $item->get_variation_id()
            : $item->get_product_id();

        return (string) self::objects()->encode("Product", (string) $productId);
    }

    /**
     * Encode Price with Tax Mode detection
     *
     * @param mixed $amount
     * @param mixed $taxAmount
     * @param mixed $quantity
     *
     * @return null|array
     */
    private function encodePrice($amount, $taxAmount, $quantity = 1): ?array
    {
        if (is_numeric($amount) && is_numeric($quantity) && 0 != $quantity) {
            $totalHT = (double) ($amount / $quantity);
        } else {
            $totalHT = 0.0;
        }
        if (is_numeric($amount) && is_numeric($taxAmount) && 0 != $amount) {
            $vatPercent = (double) ($amount  ? (100 * (float) $taxAmount / $amount) : 0);
        } else {
            $vatPercent = 0.0;
        }
        $price = self::prices()->encode(
            $totalHT,                               // Tax Excl.
            $vatPercent,                            // VAT
            null,                                   // Tax Incl.
            get_woocommerce_currency(),             // Currency
            get_woocommerce_currency_symbol()       // Symbol
        );

        return is_array($price) ? $price : null;
    }

    /**
     * Detect Tax Name for Order Item
     *
     * @param WC_Order_Item_Fee|WC_Order_Item_Product|WC_Order_Item_Shipping $item
     *
     * @return null|string
     */
    private function encodeTaxName(WC_Order_Item $item): ?string
    {
        $taxes = $item->get_taxes();
        if (empty($taxes)) {
            return null;
        }
        $taxes["total"] = array_filter($taxes["total"]);
        foreach ($taxes["total"] as $taxId => &$taxValue) {
            $taxValue = WC_Tax::get_rate_label($taxId);
        }

        return implode("|", $taxes["total"] ?? array());
    }

    /**
     * Get Given Item Full Name
     *
     * @param WC_Order_Item $itemData Woo Order Item Data
     *
     * @return string
     */
    private function getItemName(WC_Order_Item $itemData): string
    {
        //====================================================================//
        // Init with Base Item name
        $itemName = $itemData->get_name();

        //====================================================================//
        // Collect Formatted Metadata
        $itemMetas = apply_filters(
            'woocommerce_order_item_get_formatted_meta_data',
            $itemData->get_meta_data(),
            $itemData
        );
        if (!is_array($itemMetas) || empty($itemMetas)) {
            return $itemName;
        }
        //====================================================================//
        // Walk on Metadata
        $itemOptions = array();
        foreach ($itemMetas as $itemMeta) {
            //====================================================================//
            // Add Meta Infos to Item Name
            $itemMetaStr = $this->extractItemNameFromMeta($itemMeta);
            if (!empty($itemMetaStr)) {
                $itemOptions[] = $itemMetaStr;
            }
        }
        if (!empty($itemOptions)) {
            $itemName .= ' ('.implode(' | ', $itemOptions).')';
        }

        return $itemName;
    }

    /**
     * Get Given Item Full Name
     *
     * @param stdClass|WC_Meta_Data $itemMeta
     *
     * @return null|string
     */
    private function extractItemNameFromMeta($itemMeta): ?string
    {
        $metaName = null;
        $metaValue = null;
        //====================================================================//
        // Extra Product Options or Others
        if ($itemMeta instanceof stdClass) {
            $metaName = $itemMeta->display_key ?? $itemMeta->key;
            $metaValue = $itemMeta->value ?? null;
        }
        //====================================================================//
        // Standard Item Meta Data
        if ($itemMeta instanceof WC_Meta_Data) {
            $itemMeta = $itemMeta->get_data();
            $metaName = $itemMeta["key"];
            $metaValue = $itemMeta["value"];
        }
        //====================================================================//
        // Filter Meta Infos
        if (empty($metaName) || empty($metaValue) || !is_scalar($metaValue)) {
            return null;
        }
        if (0 === strpos(trim($metaName), "_")) {
            return null;
        }
        //====================================================================//
        // Add Meta Infos to Item Name
        return trim($metaName).": ".trim((string) $metaValue);
    }
}
