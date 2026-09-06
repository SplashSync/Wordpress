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
 * Dictionary of Splash Custom Fields Limits
 */
class CustomFields
{
    /**
     * Default Maximum Number of Custom Fields Exposed per Object Type
     */
    public const MAX_FIELDS = 200;

    /**
     * Get Maximum Number of Custom Fields Exposed per Object Type
     *
     * Sites with many custom fields (ACF & similar) can raise the limit from
     * the plugin settings page (Custom Fields Limit) or via the
     * splash_custom_fields_limit filter. Defaults to MAX_FIELDS.
     *
     * @return int
     */
    public static function getLimit(): int
    {
        $limit = (int) get_option('splash_custom_fields_limit', self::MAX_FIELDS);
        $limit = (int) apply_filters('splash_custom_fields_limit', ($limit > 0) ? $limit : self::MAX_FIELDS);

        return ($limit > 0) ? $limit : self::MAX_FIELDS;
    }
}
