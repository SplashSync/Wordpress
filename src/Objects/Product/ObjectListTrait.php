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

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore as Splash;
use Splash\Local\Services\MultiLang\MultiLangManager;
use WC_Product;
use WP_Post;
use WP_Query;

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
        $queryArgs = $this->toListQueryArgs($params);
        if (!empty($filter)) {
            $filteredIds = $this->resolveFilteredProductIds((string) $filter, $queryArgs);
            // Empty post__in means no filter: use impossible ID instead
            $queryArgs['post__in'] = $filteredIds ?: array(0);
        }
        //====================================================================//
        // Execute DataBase Query
        $query = new WP_Query($queryArgs);
        /** @var WP_Post[] $rawData */
        $rawData = $query->posts;
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
        // Total comes from the same query: pages & total always match
        $data["meta"]["total"] = $query->found_posts;
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

    /**
     * Build Products List Query Args
     *
     * Every filtering is done by the SQL query itself, never after it:
     * pagination & total count must apply to the very same set of products.
     * Multi-lang translations are excluded by the active Multi-lang provider.
     *
     * @param array $params Splash List Parameters (max, offset, sortfield, sortorder)
     *
     * @return array
     */
    private function toListQueryArgs(array $params): array
    {
        $order = (!empty($params["sortorder"]) && is_string($params["sortorder"]))
            ? strtoupper($params["sortorder"])
            : 'ASC'
        ;
        $sortField = (!empty($params["sortfield"]) && is_string($params["sortfield"]))
            ? $params["sortfield"]
            : 'ID'
        ;

        return MultiLangManager::getProvider()->applyListFilter(array(
            'post_type' => $this->postSearchType,
            'post_status' => array_keys(get_post_statuses()),
            'posts_per_page' => (!empty($params["max"]) ? (int) $params["max"] : 10),
            'offset' => (!empty($params["offset"]) ? (int) $params["offset"] : 0),
            //====================================================================//
            // Products ID is always the last sort criteria: posts sharing the same
            // value (i.e. same date) must keep the same order from one page to the next.
            // WP_Query keys are case-sensitive: "id" is unknown and falls back to post date.
            'orderby' => array(
                ('id' == strtolower($sortField)) ? 'ID' : $sortField => $order,
                'ID' => $order,
            ),
            //====================================================================//
            // Variable products are replaced by their variations
            'post__not_in' => self::getVariationsParentsIds(),
            'suppress_filters' => false,
        ));
    }

    /**
     * Get IDs of all Products owning Variations
     *
     * SQL equivalent of isBaseProduct(): these products are never listed,
     * their variations are listed instead.
     *
     * @return int[]
     */
    private static function getVariationsParentsIds(): array
    {
        global $wpdb;

        /** @var string[] $parentIds */
        $parentIds = $wpdb->get_col(
            "SELECT DISTINCT post_parent FROM {$wpdb->posts}"
            ." WHERE post_type = 'product_variation' AND post_parent > 0"
            ." AND post_status NOT IN ('trash', 'auto-draft')"
        );

        return array_map('intval', $parentIds);
    }

    /**
     * Resolve List Filter to Products IDs: Searched by SKU (LIKE) or Title
     *
     * @param string $filter    List Filter String
     * @param array  $queryArgs Base Posts Query Args
     *
     * @return int[] Matched Products IDs
     */
    private function resolveFilteredProductIds(string $filter, array $queryArgs): array
    {
        //====================================================================//
        // Search by SKU: partial match. Variations must be requested
        // explicitly, wc_get_products excludes them by default.
        /** @var int[] $skuIds */
        $skuIds = wc_get_products(array(
            'sku' => $filter,
            'type' => array_merge(array_keys(wc_get_product_types()), array('variation')),
            'limit' => 100,
            'return' => 'ids',
        ));
        //====================================================================//
        // Search by Post Title (Legacy Behavior)
        /** @var int[] $titleIds */
        $titleIds = get_posts(array_merge($queryArgs, array(
            's' => $filter,
            'fields' => 'ids',
            'posts_per_page' => 100,
            'offset' => 0,
        )));

        //====================================================================//
        // Merge Both Results
        return array_unique(array_merge($skuIds, $titleIds));
    }
}
