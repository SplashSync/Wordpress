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

namespace Splash\Local\Services\MultiLang;

use Splash\Local\Dictionary\MultiLangPlugins;
use WP_Post;

/**
 * WPML Plugin
 *
 * One post per language. Splash works on the default language post,
 * other languages are exposed read-only from translations.
 * WPML public API is made of filters: returned values are checked, not trusted.
 */
class WpmlProvider extends AbstractProvider
{
    /**
     * Cache of Translations Posts, indexed by "{postId}-{isoCode}"
     *
     * @var array<string, null|WP_Post>
     */
    private array $translations = array();

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return MultiLangPlugins::WPML;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultLanguage(): string
    {
        $language = apply_filters('wpml_default_language', null);

        return is_string($language) ? $language : get_locale();
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLanguages(): array
    {
        $result = array();
        $languages = apply_filters('wpml_active_languages', null, 'orderby=id');
        if (is_array($languages)) {
            foreach ($languages as $language) {
                $result[] = (string) $language["language_code"];
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     */
    public function isWritableLanguage(string $isoCode): bool
    {
        return $this->getDefaultLanguage() == $isoCode;
    }

    /**
     * @inheritDoc
     */
    public function getMasterId(int $postId): int
    {
        $masterId = apply_filters('translate_object_id', $postId, 'post', true, $this->getDefaultLanguage());

        return is_numeric($masterId) ? (int) $masterId : $postId;
    }

    /**
     * @inheritDoc
     */
    public function readPostValue(WP_Post $subject, string $fieldName, string $isoCode): ?string
    {
        $value = $subject->{$fieldName} ?? null;
        //====================================================================//
        // Default Language => Use Current Post
        if ($this->getDefaultLanguage() == $isoCode) {
            return is_scalar($value) ? (string) $value : null;
        }
        //====================================================================//
        // Others => Use Translation, fallback to Current Post
        $translation = $this->getTranslation($subject, $isoCode);
        $translated = $translation ? ($translation->{$fieldName} ?? null) : null;
        if (!empty($translated) && is_scalar($translated)) {
            return (string) $translated;
        }

        return is_scalar($value) ? (string) $value : null;
    }

    /**
     * @inheritDoc
     */
    public function decodeValue(string $value, string $isoCode, ?string $origin = null): ?string
    {
        //====================================================================//
        // Extra Languages Values are Read Only
        if (!$this->isWritableLanguage($isoCode)) {
            return (string) $origin;
        }

        return $value;
    }

    /**
     * Get Translation of a Post
     *
     * @param WP_Post $subject Master Post
     * @param string  $isoCode Language Iso Code
     *
     * @return null|WP_Post
     */
    private function getTranslation(WP_Post $subject, string $isoCode): ?WP_Post
    {
        $translationId = apply_filters('wpml_object_id', $subject->ID, $subject->post_type, true, $isoCode);
        if (!is_numeric($translationId) || ($subject->ID == $translationId)) {
            return $subject;
        }
        $cacheKey = $subject->ID."-".$isoCode;
        if (!array_key_exists($cacheKey, $this->translations)) {
            $translation = get_post((int) $translationId);
            $this->translations[$cacheKey] = ($translation instanceof WP_Post) ? $translation : null;
        }

        return $this->translations[$cacheKey];
    }
}
