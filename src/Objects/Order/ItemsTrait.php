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

namespace Splash\Local\Objects\Order;

use stdClass;
use WC_Meta_Data;
use WC_Order_Item_Product;

/**
 * WooCommerce Order Items Data Access
 */
trait ItemsTrait
{
    /**
     * @var WC_Order_Item_Product
     */
    private $item;

    /**
     * @var array
     */
    private $items;

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildItemsFields()
    {
        $groupName = __("Items");

        //====================================================================//
        // Order Line Description
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
            ->Identifier("name")
            ->InList("items")
            ->Name(__("Item"))
            ->Group($groupName)
            ->MicroData("http://schema.org/partOfInvoice", "description")
            ->Association("name@items", "quantity@items", "subtotal@items");

        //====================================================================//
        // Order Line Product Identifier
        $this->fieldsFactory()->Create((string) self::objects()->Encode("Product", SPL_T_ID))
            ->Identifier("product")
            ->InList("items")
            ->Name(__("Product"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Product", "productID")
            ->Association("name@items", "quantity@items", "subtotal@items")
            ->isNotTested();

        //====================================================================//
        // Order Line Quantity
        $this->fieldsFactory()->Create(SPL_T_INT)
            ->Identifier("quantity")
            ->InList("items")
            ->Name(__("Quantity"))
            ->Group($groupName)
            ->MicroData("http://schema.org/QuantitativeValue", "value")
            ->Association("name@items", "quantity@items", "subtotal@items");

        //====================================================================//
        // Order Line Discount
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
            ->Identifier("discount")
            ->InList("items")
            ->Name(__("Percentage discount"))
            ->Group($groupName)
            ->MicroData("http://schema.org/Order", "discount")
            ->Association("name@items", "quantity@items", "subtotal@items");

        //====================================================================//
        // Order Line Unit Price
        $this->fieldsFactory()->Create(SPL_T_PRICE)
            ->Identifier("subtotal")
            ->InList("items")
            ->Name(__("Price"))
            ->Group($groupName)
            ->MicroData("http://schema.org/PriceSpecification", "price")
            ->Association("name@items", "quantity@items", "subtotal@items");

        //====================================================================//
        // Order Line Tax Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->Identifier("tax_name")
            ->InList("items")
            ->Name(__("Tax name"))
            ->MicroData("http://schema.org/PriceSpecification", "valueAddedTaxName")
            ->Group($groupName)
            ->Association("name@items", "quantity@items", "subtotal@items")
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
    private function getItemsFields($key, $fieldName)
    {
        // Check if List field & Init List Array
        $fieldId = self::lists()->InitOutput($this->out, "items", $fieldName);
        if (!$fieldId) {
            return;
        }

        foreach ($this->loadAllItems() as $index => $item) {
            if (is_a($item, "WC_Order_Item_Product")) {
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

    /**
     * Read Order Item Field
     *
     * @param mixed $item
     * @param mixed $fieldId
     *
     * @return mixed
     */
    private function getProductItemData($item, $fieldId)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldId) {
            case 'name':
                return  $this->getItemName($item);
            case 'quantity':
                return  $item->get_quantity();
            case 'price':
                return  $this->encodePrice($item->get_total(), $item->get_total_tax(), $item->get_quantity());
            case 'tax_name':
                return  $this->encodeTaxName($item);
            case 'discount':
                // Compute Discount (Precent of Total to SubTotal)
                $discount = 100 * ($item->get_subtotal() - $item->get_total()) / $item->get_subtotal();

                return  round((double) $discount, 2);
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
     * @param mixed $item
     * @param mixed $fieldId
     *
     * @return mixed
     */
    private function getItemData($item, $fieldId)
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
                // Compute Discount (Precent of Total to SubTotal)
                return   (double) 0;
            case 'product':
                return null;
        }

        return null;
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setItemsFields($fieldName, $fieldData)
    {
        // Check if List field
        if ("items" != $fieldName) {
            return;
        }

        // Load Initial Version
        $this->loadAllItems();

        foreach ($fieldData as $itemData) {
            $this->item = array_shift($this->items);
            //====================================================================//
            // Create Item If Needed
            if (! $this->item) {
                $this->item = new WC_Order_Item_Product();
                $this->object->add_item($this->item);
            }
            //====================================================================//
            // Update Item
            if (is_a($this->item, "WC_Order_Item_Product")) {
                $this->setProductItem($itemData);
            } else {
                $this->setItem($itemData);
            }
        }

        foreach ($this->items as $item) {
            $this->object->remove_item($item);
        }

        unset($this->in["items"]);
    }

    /**
     * Write Given Product Item Fields
     *
     * @param mixed $itemData Field Data
     *
     * @return void
     */
    private function setProductItem($itemData)
    {
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
            $productId = self::objects()->Id($itemData["product"]);
            $this->setGeneric("_product_id", $productId, "item");
        }
        //====================================================================//
        // Update Unit Price
        if (isset($itemData["subtotal"])) {
            // Compute Expected Subtotal
            $subtotal = $this->item->get_quantity() * self::prices()->TaxExcluded($itemData["subtotal"]);
            // Compute Expected Subtotal Tax Incl.
            $subtotalTax = $this->item->get_quantity() * self::prices()->TaxAmount($itemData["subtotal"]);
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
     * @param mixed $itemData Field Data
     *
     * @return void
     */
    private function setItem($itemData)
    {
        //====================================================================//
        // Update Name
        if (isset($itemData["name"])) {
            $this->setGeneric("_name", $itemData["name"], "item");
        }
        //====================================================================//
        // Update Quantity
        if (isset($itemData["quantity"])) {
            $qty = $itemData["quantity"];
        } else {
            $qty = 1;
        }
        //====================================================================//
        // Update Unit Price
        if (isset($itemData["subtotal"])) {
            // Compute Expected Total
            $total = $qty * self::prices()->TaxExcluded($itemData["subtotal"]);
            // Compute Expected Total Tax Incl.
            $totalTax = $qty * self::prices()->TaxAmount($itemData["subtotal"]);
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
     * @param string $row    Tax Row Id
     * @param float  $amount Tax Amount
     *
     * @return void
     */
    private function setItemTaxArray($row, $amount)
    {
        $taxes = $this->item->get_taxes();
        if (empty($taxes[$row])) {
            $taxes[$row] = array( 0 => $amount );
        } else {
            foreach ($taxes[$row] as &$value) {
                $value = $amount;
                $amount = 0;
            }
        }
        $this->item->set_taxes($taxes);
        $this->needUpdate();
    }

    /**
     * Write Given Tax Amount to Tax Array Row
     *
     * @param float $total    Product Line Total Price
     * @param float $subtotal Product line Subtotal
     *
     * @return void
     */
    private function setProductTaxArray($total, $subtotal)
    {
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
                $total = 0;
            }
        }

        $this->item->set_taxes($taxes);
        $this->needUpdate();
    }

    /**
     * Load All Order Items
     *
     * @return array
     */
    private function loadAllItems()
    {
        $this->items = array_merge(
            $this->object->get_items(),
            $this->object->get_items("shipping"),
            $this->object->get_items("fee")
        );

        return $this->items;
    }

    /**
     * Detect Product Id for Order Item
     *
     * @param mixed $item
     *
     * @return null|string
     */
    private function encodeProductId($item)
    {
        if (! $item->get_product_id()) {
            return null;
        }
        $productId = ($item->get_variation_id())
            ? $item->get_variation_id()
            : $item->get_product_id();

        return (string) self::objects()->encode("Product", $productId);
    }

    /**
     * Encode Price with Tax Mode detection
     *
     * @param mixed $amount
     * @param mixed $taxAmount
     * @param mixed $quantity
     *
     * @return array|string
     */
    private function encodePrice($amount, $taxAmount, $quantity = 1)
    {
        if (is_numeric($amount) && is_numeric($quantity) && 0 != $quantity) {
            $totalHT = (double) ($amount / $quantity);
        } else {
            $totalHT = (double) 0;
        }
        if (is_numeric($amount) && is_numeric($taxAmount) && 0 != $amount) {
            $vatPercent = (double) ($amount  ? (100 * (float) $taxAmount / $amount) : 0);
        } else {
            $vatPercent = (double) 0;
        }
        $totalTTC = null;

        return   self::prices()
            ->encode(
                $totalHT,                               // Tax Excl.
                $vatPercent,                            // VAT
                $totalTTC,                              // Tax Incl.
                get_woocommerce_currency(),             // Currency
                get_woocommerce_currency_symbol()       // Symbol
            );
    }

    /**
     * Detect Tax Name for Order Item
     *
     * @param mixed $item
     *
     * @return null|string
     */
    private function encodeTaxName($item)
    {
        $taxes = $item->get_taxes();
        if (empty($taxes)) {
            return null;
        }
        foreach ($taxes["total"] as $taxId => &$taxValue) {
            $taxValue = \WC_Tax::get_rate_label($taxId);
        }

        return implode("|", $taxes["total"]);
    }

    /**
     * Get Given Item Full Name
     *
     * @param mixed $itemData Woo Order Item Data
     *
     * @return string
     */
    private function getItemName($itemData)
    {
        //====================================================================//
        // Init with Base Item name
        $itemName = $itemData->get_name();

        //====================================================================//
        // Collect Formated Metadata
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
            $itemMetaStr = $this->extractItemNamefromMeta($itemMeta);
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
     * @param mixed $itemMeta
     *
     * @return null|string
     */
    private function extractItemNamefromMeta($itemMeta)
    {
        $metaName = null;
        $metaValue = null;
        //====================================================================//
        // Extra Product Options or Others
        if ($itemMeta instanceof stdClass) {
            $metaName = isset($itemMeta->display_key) ? $itemMeta->display_key : $itemMeta->key;
            $metaValue = isset($itemMeta->value) ? $itemMeta->value : null;
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
        if (empty($metaName) || empty($metaValue)) {
            return null;
        }
        if (0 === strpos(trim($metaName), "_")) {
            return null;
        }
        //====================================================================//
        // Add Meta Infos to Item Name
        return trim($metaName).": ".trim($metaValue);
    }
}
