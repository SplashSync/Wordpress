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

namespace Splash\Local\Objects\Core;

use WP_Post;

/**
 * WordPress Wpml Plugin Trait
 */
trait WpmlTrait
{
    /**
     * Cache for Duplicates Posts
     *
     * @var array<int, null|WP_Post>
     */
    private static $duplicatesCache = array();

    /**
     * Get Wpml Master Post ID
     *
     * @param int $postId Wp Post ID
     *
     * @return int
     */
    public static function getWpmlMaster(int $postId): int
    {
        /** @phpstan-ignore-next-line */
        return (int) apply_filters('translate_object_id', $postId, 'post', true, self::getDefaultLanguage());
    }

    /**
     * Check if Post is Wpml Master
     *
     * @param int $postId Wp Post ID
     *
     * @return bool
     */
    public static function isWpmlMaster(int $postId): bool
    {
        return self::getWpmlMaster($postId) == $postId;
    }

    /**
     * Detect Default Language
     *
     * @return string
     */
    protected static function getWpmlDefaultLanguage(): string
    {
        /** @phpstan-ignore-next-line */
        return apply_filters('wpml_default_language', null);
    }

    /**
     * Encode Wpml String to Splash MultiLang Array
     *
     * @param WP_Post $subject   Subject
     * @param string  $fieldName Field Name
     * @param string  $isoCode   Language Iso Code
     *
     * @return null|string
     */
    protected static function getWpmlValue(WP_Post $subject, string $fieldName, string $isoCode): ?string
    {
        //====================================================================//
        // Default  => Use Current Object
        if (self::isDefaultLanguage($isoCode)) {
            return $subject->{$fieldName};
        }
        //====================================================================//
        // Others  => Get Data from Translations Duplicates
        $duplicate = self::getWpmlDuplicate($subject, $isoCode);

        return !empty($duplicate->{$fieldName}) ? $duplicate->{$fieldName} : $subject->{$fieldName};
    }

    /**
     * Update Wpml String
     *
     * @param string      $fieldData Splash MultiLang Field Data
     * @param null|string $isoCode   Language Iso Code
     * @param null|string $origin    Original Wp MultiLang Data
     *
     * @return string
     */
    protected static function setWpmlValue(string $fieldData, ?string $isoCode, ?string $origin = null): string
    {
        //====================================================================//
        // Extra Language Values are Read Only
        if (!is_null($isoCode) && !self::isDefaultLanguage($isoCode)) {
            return (string) $origin;
        }

        return $fieldData;
    }

    /**
     * Count number of Posts with Wpml Duplicates Filtering
     *
     * @param string $postType
     *
     * @return int
     */
    protected function countPostsByTypesNoDuplicates(string $postType): int
    {
        global $wpdb;

        $dbPrefix = $wpdb->prefix;
        //====================================================================//
        // Adjust post type to format WPML uses
        $postType = 'post_'.$postType;
        //====================================================================//
        // Prepare Wp db Query
        $query = "SELECT COUNT( ".$dbPrefix."posts.ID )";
        $query .= " FROM ".$dbPrefix."posts";
        $query .= " LEFT JOIN ".$dbPrefix."icl_translations";
        $query .= " ON ".$dbPrefix."posts.ID = ".$dbPrefix."icl_translations.element_id";
        $query .= " WHERE ".$dbPrefix."icl_translations.source_language_code IS NULL";
        $query .= " AND ".$dbPrefix."icl_translations.element_type = '".$postType."'";
        $query .= " AND ".$dbPrefix."posts.post_status != 'trash'";
        //====================================================================//
        // Execute Query
        return (int) $wpdb->get_var($query);
    }

    /**
     * Detect Default Language
     *
     * @param WP_Post $subject
     * @param string  $isoCode
     *
     * @return WP_Post
     */
    private static function getWpmlDuplicate(WP_Post $subject, string $isoCode): ?WP_Post
    {
        //====================================================================//
        // Get Translations Duplicate for Post
        /** @phpstan-ignore-next-line */
        $duplicateId = (int) apply_filters('wpml_object_id', $subject->ID, $subject->post_type, true, $isoCode);
        //====================================================================//
        // Is Same Post => Use It
        if ($subject->ID == $duplicateId) {
            return $subject;
        }
        //====================================================================//
        // Load Duplicate to cache
        if (!isset(self::$duplicatesCache[$duplicateId])) {
            $duplicate = get_post($duplicateId);
            self::$duplicatesCache[$subject->ID] = ($duplicate instanceof WP_Post) ? $duplicate : null;
        }

        return self::$duplicatesCache[$subject->ID];
    }
}
