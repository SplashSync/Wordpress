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

namespace Splash\Local\Core;

/**
 * Manage Selection of Custom Fields Exposed to Splash
 *
 * Without selection, every meta key found for an object type is exposed,
 * up to CustomFields::MAX_FIELDS for posts. A site may instead choose which
 * meta keys are exposed, per object type, from the plugin settings page
 * (option splash_cf_{type}_fields) or the splash_custom_fields_selection filter.
 *
 * One rule per line (or comma separated):
 *  - "my_key"      Expose this meta key, even before any object uses it
 *  - "acf_*"       Expose discovered meta keys matching this pattern
 *  - "*"           Expose all discovered meta keys
 *  - "!acf_tmp_*"  Never expose matching meta keys
 *  - "# comment"   Ignored
 *
 * As soon as one key or pattern is selected, the limit no longer applies.
 * Exclusions alone only hide keys: the limit still applies to the others.
 */
class CustomFieldsManager
{
    /**
     * Option: Custom Fields Selection Rules, completed with Object Type & Suffix
     */
    public const OPT_PREFIX = "splash_cf_";

    /**
     * Option: Custom Fields Selection Rules Suffix
     */
    public const OPT_SUFFIX = "_fields";

    /**
     * Filter: Custom Fields Selection Rules
     */
    public const FILTER = "splash_custom_fields_selection";

    /**
     * Object Type for Users (ThirdParty) Custom Fields
     */
    public const USER = "user";

    /**
     * Rule Prefix: Exclude Matching Meta Keys
     */
    public const EXCLUDE = "!";

    /**
     * Rule Wildcard: Any Characters
     */
    public const WILDCARD = "*";

    /**
     * Rule Prefix: Comment
     */
    public const COMMENT = "#";

    /**
     * Get Custom Fields Selection Rules for an Object Type
     *
     * @param string $objectType Object Type (product, order, invoice, post, page, user...)
     *
     * @return string[]
     */
    public static function getRules(string $objectType): array
    {
        //====================================================================//
        // Load Rules from Plugin Settings
        $option = \get_option(self::OPT_PREFIX.$objectType.self::OPT_SUFFIX, "");
        $rules = self::parseRules(is_string($option) ? $option : "");
        //====================================================================//
        // Let Sites Complete or Override Rules by Code
        $rules = \apply_filters(self::FILTER, $rules, $objectType);

        return is_array($rules) ? self::cleanRules($rules) : array();
    }

    /**
     * Parse Custom Fields Selection Rules from Settings Text
     *
     * @param string $rules One Rule per Line, or Comma Separated
     *
     * @return string[]
     */
    public static function parseRules(string $rules): array
    {
        return self::cleanRules((array) preg_split("/[\r\n,]+/", $rules));
    }

    /**
     * Check if Rules Select Meta Keys (i.e. not only Exclusions)
     *
     * @param string[] $rules
     *
     * @return bool
     */
    public static function hasSelection(array $rules): bool
    {
        return !empty(self::getIncluded($rules));
    }

    /**
     * Apply Selection Rules to Discovered Meta Keys
     *
     * Without selected keys or patterns, discovered keys are returned minus
     * exclusions. Otherwise, only discovered keys matching a selected pattern
     * are returned, plus selected exact keys not discovered yet.
     *
     * @param string[] $metaKeys Discovered Meta Keys
     * @param string[] $rules    Selection Rules
     *
     * @return string[]
     */
    public static function select(array $metaKeys, array $rules): array
    {
        $included = self::getIncluded($rules);
        $excluded = self::getExcluded($rules);
        //====================================================================//
        // Walk on Discovered Meta Keys
        $selected = array();
        foreach ($metaKeys as $index => $metaKey) {
            if (!empty($included) && !self::isMatching($metaKey, $included)) {
                continue;
            }
            if (self::isMatching($metaKey, $excluded)) {
                continue;
            }
            $selected[$index] = $metaKey;
        }
        //====================================================================//
        // Add Selected Exact Keys, even if not Discovered
        foreach ($included as $pattern) {
            if (false !== strpos($pattern, self::WILDCARD)) {
                continue;
            }
            if (in_array($pattern, $selected, true) || self::isMatching($pattern, $excluded)) {
                continue;
            }
            $selected[] = $pattern;
        }

        return $selected;
    }

    /**
     * Check if a Meta Key Matches one of the Given Patterns
     *
     * @param string   $metaKey
     * @param string[] $patterns
     *
     * @return bool
     */
    public static function isMatching(string $metaKey, array $patterns): bool
    {
        foreach ($patterns as $pattern) {
            if (false === strpos($pattern, self::WILDCARD)) {
                if ($pattern === $metaKey) {
                    return true;
                }

                continue;
            }
            $regex = "/^".str_replace("\\".self::WILDCARD, ".*", preg_quote($pattern, "/"))."$/s";
            if (preg_match($regex, $metaKey)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get Selected Keys & Patterns
     *
     * @param string[] $rules
     *
     * @return string[]
     */
    private static function getIncluded(array $rules): array
    {
        return array_values(array_filter($rules, function (string $rule): bool {
            return 0 !== strpos($rule, self::EXCLUDE);
        }));
    }

    /**
     * Get Excluded Keys & Patterns
     *
     * @param string[] $rules
     *
     * @return string[]
     */
    private static function getExcluded(array $rules): array
    {
        $excluded = array();
        foreach ($rules as $rule) {
            if (0 === strpos($rule, self::EXCLUDE)) {
                $pattern = trim(substr($rule, strlen(self::EXCLUDE)));
                if ("" !== $pattern) {
                    $excluded[] = $pattern;
                }
            }
        }

        return $excluded;
    }

    /**
     * Clean Rules: Trim, Remove Empty Rules, Comments & Duplicates
     *
     * @param array $rules
     *
     * @return string[]
     */
    private static function cleanRules(array $rules): array
    {
        $cleaned = array();
        foreach ($rules as $rule) {
            if (!is_string($rule)) {
                continue;
            }
            $rule = trim($rule);
            if (("" === $rule) || (0 === strpos($rule, self::COMMENT)) || in_array($rule, $cleaned, true)) {
                continue;
            }
            $cleaned[] = $rule;
        }

        return $cleaned;
    }
}
