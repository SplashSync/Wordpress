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

namespace Splash\Local\Interfaces;

use WP_Post;

/**
 * Access to a WordPress Multi-lang Plugin
 *
 * Two storage models exist:
 * - inline: all languages are stored in the same post (WP Multilang)
 * - duplicates: one post per language, linked as translations (WPML, Polylang)
 *
 * Splash only works on master posts: the post in the default language.
 */
interface MultiLangProviderInterface
{
    /**
     * Get Provider Code
     *
     * @return string One of MultiLangPlugins constants
     */
    public function getCode(): string;

    //====================================================================//
    // Languages
    //====================================================================//

    /**
     * Get Default Language Iso Code
     *
     * @return string
     */
    public function getDefaultLanguage(): string;

    /**
     * Get Languages exposed on Multi-lang Fields, default language included
     *
     * @return string[]
     */
    public function getAvailableLanguages(): array;

    /**
     * Check if Values in this Language can be written
     *
     * @param string $isoCode Language Iso Code
     *
     * @return bool
     */
    public function isWritableLanguage(string $isoCode): bool;

    //====================================================================//
    // Master Posts & Translations
    //====================================================================//

    /**
     * Get ID of the Master Post: the post in default language
     *
     * A post without translation in default language is its own master.
     *
     * @param int $postId WordPress Post ID
     *
     * @return int
     */
    public function getMasterId(int $postId): int;

    /**
     * Check if Post is a Master Post
     *
     * @param int $postId WordPress Post ID
     *
     * @return bool
     */
    public function isMaster(int $postId): bool;

    /**
     * Restrict a Posts Query to Master Posts
     *
     * Filtering is done by the query itself, so pagination & counts stay right.
     *
     * @param array $queryArgs WP_Query Arguments
     *
     * @return array
     */
    public function applyListFilter(array $queryArgs): array;

    /**
     * Complete a Post just created by Splash
     *
     * @param int $postId WordPress Post ID
     *
     * @return void
     */
    public function onPostCreated(int $postId): void;

    //====================================================================//
    // Values
    //====================================================================//

    /**
     * Extract Value for a Language from a Raw Stored Value
     *
     * @param string $rawValue Value as stored in database
     * @param string $isoCode  Language Iso Code
     *
     * @return null|string
     */
    public function encodeValue(string $rawValue, string $isoCode): ?string;

    /**
     * Read a Post Property in a Language
     *
     * @param WP_Post $subject   Master Post
     * @param string  $fieldName Post Property Name
     * @param string  $isoCode   Language Iso Code
     *
     * @return null|string
     */
    public function readPostValue(WP_Post $subject, string $fieldName, string $isoCode): ?string;

    /**
     * Merge a Value for a Language into the Raw Stored Value
     *
     * @param string      $value   New Value for this Language
     * @param string      $isoCode Language Iso Code
     * @param null|string $origin  Current Raw Stored Value
     *
     * @return null|string New Raw Value to Store
     */
    public function decodeValue(string $value, string $isoCode, ?string $origin = null): ?string;
}
