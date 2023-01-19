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

namespace   Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;
use Splash\Local\Core\PluginManager;
use WC_Product;
use WP_Post;

/**
 * WooCommerce Product Object List
 */
trait ObjectListTrait
{
    /**
     * {@inheritdoc}
     */
    public function objectsList(string $filter = null, array $params = array()): array
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
            'suppress_filters' => false,
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
            if (self::isObjectsListFiltered($product)) {
                unset($rawData[$key]);

                continue;
            }

            $data[] = $this->getObjectsListData($product);
        }

        //====================================================================//
        // Store Meta Total & Current values
        $data["meta"]["total"] = $this->countProducts();
        $data["meta"]["current"] = count($rawData);

        Splash::log()->deb("MsgLocalTpl", __CLASS__, __FUNCTION__, " ".count($rawData)." Post Found.");

        return $data;
    }

    /**
     * Count Number of Products with Wpml Compat
     *
     * @return int
     */
    protected function countProducts(): int
    {
        //====================================================================//
        // Without Wpml
        if (!PluginManager::hasWpml()) {
            return $this->countPostsByTypes($this->postSearchType);
        }
        //====================================================================//
        // Wpml Activated => Walk on Posts Types
        $total = 0;
        foreach ($this->postSearchType as $postType) {
            $total += self::countPostsByTypesNoDuplicates($postType);
        }

        return $total;
    }

    /**
     * Build Product List Data
     *
     * @param WP_Post $product
     *
     * @return array
     */
    private function getObjectsListData(WP_Post $product): array
    {
        //====================================================================//
        // Detect Unknown Status
        $statuses = get_page_statuses();
        $status = $statuses[$product->post_status] ?? "...?";
        /** @var WC_Product */
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
            "md5" => $this->getMd5Checksum($wcProduct)
        );
    }

    /**
     * Check if Product is Allowed for List Data
     *
     * @param WP_Post $product
     *
     * @return bool
     */
    private static function isObjectsListFiltered(WP_Post $product): bool
    {
        //====================================================================//
        // Filter Variants Base Products from results
        if (("product" == $product->post_type) && self::isBaseProduct($product->ID)) {
            return true;
        }
        //====================================================================//
        // Filter Languages Duplicates from results
        if (!self::isMultiLangMaster($product->ID)) {
            return true;
        }

        return false;
    }
}
