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

use WP_Post;

trait PrimaryTrait
{
    /**
     * @inheritDoc
     */
    public function getByPrimary(array $keys): ?string
    {
        //====================================================================//
        // Extract Primary Key
        $sku = $keys['_sku'] ?? null;
        if (!$sku) {
            return null;
        }
        //====================================================================//
        // Prepare Query Args
        $queryArgs = array(
            'post_type' => $this->postSearchType,
            'post_status' => array_keys(get_post_statuses()),
            'status' => array_keys(get_post_statuses()),
            'limit' => 5,
            'suppress_filters' => false,
            'meta_query' => array(
                array('key' => '_sku', 'value' => $sku, 'compare' => '=')
            )
        );
        //====================================================================//
        // Execute DataBase Query
        $rawData = get_posts($queryArgs);
        if (empty($rawData)) {
            return null;
        }
        //====================================================================//
        // Walk on Products
        /** @var int[] $products */
        $products = array();
        /** @var WP_Post $product */
        foreach ($rawData as $product) {
            //====================================================================//
            // Filter Variants Base Products from results
            if (self::isObjectsListFiltered($product)) {
                continue;
            }

            $products[] = $product->ID;
        }
        if (1 != count($products)) {
            return null;
        }
        //====================================================================//
        // Return Product ID
        return (string) $products[0];
    }
}
