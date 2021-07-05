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

namespace   Splash\Local\Objects;

use Splash\Core\SplashCore      as Splash;
use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use WC_Product;
use WC_Product_Variable;

/**
 * WooCommerce Product Object
 *
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class Product extends AbstractObject
{
    //====================================================================//
    // Splash Php Core Traits
    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;

    //====================================================================//
    // Core Fields
    use Core\MultilangTrait;                // Multilang Fields Manager
    use Core\WooCommerceObjectTrait;        // Trigger WooCommerce Module Activation
    use Core\UnitConverterTrait;            // Wordpress Unit Converter
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
    use Product\HooksTrait;                 // Wordpress Events
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
}
