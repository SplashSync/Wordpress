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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use WC_Product;
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
     *  Object Disable Flag. Uncomment this line to Override this flag and disable Object.
     */
//    protected static    $DISABLED        =  True;

    /**
     *  Object Name (Translated by Module)
     */
    protected static $NAME = "Product";

    /**
     *  Object Description (Translated by Module)
     */
    protected static $DESCRIPTION = "WooCommerce Product Object";

    /**
     *  Object Icon (FontAwesome or Glyph ico tag)
     */
    protected static $ICO = "fa fa-product-hunt";

    /**
     *  Object Synchronization Recommended Configuration
     */
    // Enable Creation Of New Local Objects when Not Existing
    protected static $ENABLE_PUSH_CREATED = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WC_Product
     */
    protected $product;

    protected $postType = "product";
    protected $postSearchType = array( "product" , "product_variation" );

    /**
     * {@inheritdoc}
     */
    public function objectsList($filter = null, $params = null)
    {
        //====================================================================//
        // Stack Trace
        Splash::log()->trace();

        $data = array();

        //====================================================================//
        // Load From DataBase
        $rawData = get_posts(array(
            'post_type' => $this->postSearchType,
            'post_status' => array_keys(get_post_statuses()),
            'numberposts' => (!empty($params["max"])        ? $params["max"] : 10),
            'offset' => (!empty($params["offset"])     ? $params["offset"] : 0),
            'orderby' => (!empty($params["sortfield"])  ? $params["sortfield"] : 'id'),
            'order' => (!empty($params["sortorder"])  ? $params["sortorder"] : 'ASC'),
            's' => (!empty($filter)  ? $filter : ''),
        ));

        //====================================================================//
        // For each result, read information and add to $data
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
            "_stock" => get_post_meta($product->ID, "_stock", true),
            "_price" => get_post_meta($product->ID, "_price", true),
            "_regular_price" => get_post_meta($product->ID, "_regular_price", true),
            "md5" => $wcProduct ? $this->getMd5Checksum($wcProduct) : ""
        );
    }
}
