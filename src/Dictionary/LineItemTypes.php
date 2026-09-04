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

namespace Splash\Local\Dictionary;

/**
 * Dictionary of Splash Orders & Invoices Line Item Types
 *
 * Codes are hardcoded here: this module does not use Splash Scopes
 * nor PhpCore dictionaries yet.
 *
 * @deprecated This class is temporary: it will be removed during the V3
 *             migration, in favor of the official Splash Core V3 dictionary.
 */
class LineItemTypes
{
    /**
     * Product or Service Line: the default Line Type
     */
    public const PRODUCT = "product";

    /**
     * Shipping Costs Line
     */
    public const SHIPPING = "shipping";

    /**
     * Extra Fee or Discount Line
     */
    public const FEE = "fee";

    /**
     * Optional Line: Quantity is Zero, Price is not
     */
    public const OPTION = "option";

    /**
     * Comment Line: Quantity is One, Price is Zero
     */
    public const COMMENT = "comment";

    /**
     * Get List of Available Line Item Types
     *
     * @return array<string, string>
     */
    public static function getChoices(): array
    {
        return array(
            self::PRODUCT => __("Product / Service", "splash-wordpress-plugin"),
            self::SHIPPING => __("Shipping Costs", "splash-wordpress-plugin"),
            self::FEE => __("Extra Fee / Discount", "splash-wordpress-plugin"),
            self::OPTION => __("Optional Line", "splash-wordpress-plugin"),
            self::COMMENT => __("Comment Line", "splash-wordpress-plugin"),
        );
    }
}
