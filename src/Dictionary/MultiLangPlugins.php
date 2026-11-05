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
 * Dictionary of Supported Multi-lang Plugins
 *
 * Each code identifies a Multi-lang Provider service.
 */
class MultiLangPlugins
{
    /**
     * No Multi-lang Plugin: mono-lang site
     */
    public const DISABLED = "disabled";

    /**
     * WP Multilang Plugin: all languages stored inline in the same post
     */
    public const WP_MULTILANG = "wp-multilang";

    /**
     * WPML Plugin: one post per language
     */
    public const WPML = "wpml";

    /**
     * Polylang Plugin (Free or Pro): one post per language
     */
    public const POLYLANG = "polylang";
}
