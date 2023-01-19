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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use Splash\Models\Objects\PrimaryKeysAwareInterface;
use WC_Product;
use WC_Product_Variable;
use WP_Post;

/**
 * WooCommerce Product Object
 */
class Product extends AbstractObject implements PrimaryKeysAwareInterface
{
    //====================================================================//
    // Splash Php Core Traits
    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;

    //====================================================================//
    // Core Fields
    use Core\MultiLangTrait;                // Multi-lang Fields Manager
    use Core\WooCommerceObjectTrait;        // Trigger WooCommerce Module Activation
    use Core\UnitConverterTrait;            // WordPress Unit Converter
    use Core\DokanTrait;                    // Dokan Infos

    //====================================================================//
    // Post Fields
    use Post\MetaTrait;                     // Object MetaData
    use Post\ThumbTrait;                    // Thumbnail Image
    use Post\CustomTrait;                   // Custom Fields
    use Post\CounterTrait;                  // Posts Counter

    //====================================================================//
    // Products Fields
    use Product\CRUDTrait;                  // Product CRUD
    use Product\ObjectListTrait;            // Products Listing Functions
    use Product\PrimaryTrait;               // Search Product by Primary Keys
    use Product\HooksTrait;                 // WordPress Events
    use Product\CoreTrait;                  // Products Core Fields
    use Product\MainTrait;                  // Product Main Fields
    use Product\StockTrait;                 // Product Stocks
    use Product\PriceTrait;                 // Product Prices Fields
    use Product\VariantsTrait;              // Product Variants
    use Product\ChecksumTrait;              // Product CheckSum Fields
    use Product\ImagesTrait;                // Product Images
    use Product\CategoriesTrait;            // Product Categories
    use Product\WholesalePricesTrait;       // Wholesale Prices for WooCommerce by Wholesale

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static string $name = "Product";

    /**
     * {@inheritdoc}
     */
    protected static string $description = "WooCommerce Product Object";

    /**
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-product-hunt";

    /**
     * Disable Creation Of New Local Objects when Not Existing
     *
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WP_Post;
     */
    protected object $object;

    /**
     * @var WC_Product|WC_Product_Variable
     */
    protected $product;

    /**
     * @var string
     */
    protected string $postType = "product";

    /**
     * @var string[]
     */
    protected array $postSearchType = array( "product" , "product_variation" );
}
