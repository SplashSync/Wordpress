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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use WC_Product;
use WC_Product_Variable;
use WP_Post;

/**
 * WooCommerce Product Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Product extends AbstractObject
{
    // Splash Php Core Traits
    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;

    // Core Fields
    use Core\MultilangTrait;                // Multilang Fields Manager
    use Core\WooCommerceObjectTrait;        // Trigger WooCommerce Module Activation
    use Core\UnitConverterTrait;            // Wordpress Unit Converter

    // Post Fields
    use Post\MetaTrait;                     // Object MetaData
    use Post\ThumbTrait;                    // Thumbnail Image
    use Post\CustomTrait;                   // Custom Fields

    // Products Fields
    use Product\CRUDTrait;                  // Product CRUD
    use Product\HooksTrait;                 // Wordpress Events
    use Product\CoreTrait;                  // Products Core Fields
    use Product\MainTrait;                  // Product Main Feields
    use Product\StockTrait;                 // Product Stocks
    use Product\PriceTrait;                 // Product Prices Fields
    use Product\VariantsTrait;              // Product Variants
    use Product\ChecksumTrait;              // Product CheckSum Fields
    use Product\ImagesTrait;                // Product Images

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $NAME = "Product";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static $DESCRIPTION = "WooCommerce Product Object";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static $ICO = "fa fa-product-hunt";

    /**
     * Disable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static $ENABLE_PUSH_CREATED = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WC_Product|WC_Product_Variable
     */
    protected $product;

    /**
     * @var string
     */
    protected $postType = "product";

    /**
     * @var array
     */
    protected $postSearchType = array( "product" , "product_variation" );

    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();
        //====================================================================//
        // Prepare Query Args
        $queryArgs = array(
            'post_type' => $this->postSearchType,
            'post_status' => array_keys(get_post_statuses()),
            'numberposts' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
        );
        if (!empty($filter)) {
            $queryArgs['s'] = (string) $filter;
        }
        //====================================================================//
        // Execute DataBase Query
        $rawData = get_posts($queryArgs);
        //====================================================================//
        // For each result, read information and add to $data
        $data = array();
        /** @var WP_Post $product */
        foreach ($rawData as $key => $product) {
            //====================================================================//
            // Filter Variants Base Products from results
            if (("product" == $product->post_type) && $this->isBaseProduct($product->ID)) {
                unset($rawData[$key]);

                continue;
            }
            $data[] = $this->getObjectsListData($product);
        }

        //====================================================================//
        // Store Meta Total & Current values
        $totals = wp_count_posts('product');
        $data["meta"]["total"] = $totals->publish + $totals->future + $totals->draft;
        $data["meta"]["total"] += $totals->pending + $totals->private + $totals->trash;
        $varTotals = wp_count_posts("product_variation");
        $data["meta"]["total"] += $varTotals->publish + $varTotals->future + $varTotals->draft;
        $data["meta"]["total"] += $varTotals->pending + $varTotals->private + $varTotals->trash;
        $data["meta"]["current"] = count($rawData);

        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Post Found.");

        return $data;
    }

    /**
     * Build Product List Data
     *
     * @param WP_Post $product
     *
     * @return array
     */
    private function getObjectsListData($product)
    {
        //====================================================================//
        // Detect Unknown Status
        $statuses = get_page_statuses();
        $status = isset($statuses[$product->post_status]) ? $statuses[$product->post_status] : "...?";
        /** @var WC_Product $wcProduct */
        $wcProduct = wc_get_product($product->ID);
        //====================================================================//
        // Add Product Data to results
        return array(
            "id" => $product->ID,
            "post_title" => $this->extractMultilangValue($product->post_title),
            "post_name" => $product->post_name,
            "post_status" => $status,
            "_sku" => get_post_meta($product->ID, "_sku", true),
            "_stock" => get_post_meta($wcProduct->get_stock_managed_by_id(), "_stock", true),
            "_price" => get_post_meta($product->ID, "_price", true),
            "_regular_price" => get_post_meta($product->ID, "_regular_price", true),
            "md5" => empty($wcProduct) ? '' : $this->getMd5Checksum($wcProduct)
        );
    }
}
