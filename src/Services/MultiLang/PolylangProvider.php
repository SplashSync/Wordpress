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

/**
 * Polylang Plugin (Free or Pro)
 *
 * One post per language, linked as translations by Polylang core.
 * Splash only works on posts in default language: translations are
 * filtered out, values are mono-lang. Copying data to translations
 * is the job of a WooCommerce extension (Polylang for WooCommerce, Hyyan...).
 */
class PolylangProvider extends AbstractProvider
{
    /**
     * Cache of Translations Post IDs, indexed by Posts Types list
     *
     * @var array<string, int[]>
     */
    private array $translationsIds = array();

    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return MultiLangPlugins::POLYLANG;
    }

    /**
     * @inheritDoc
     */
    public function getDefaultLanguage(): string
    {
        $locale = pll_default_language('locale');

        return is_string($locale) ? $locale : get_locale();
    }

    /**
     * @inheritDoc
     */
    public function getMasterId(int $postId): int
    {
        $defaultSlug = pll_default_language();
        if (!is_string($defaultSlug)) {
            return $postId;
        }
        $masterId = pll_get_post($postId, $defaultSlug);

        return $masterId ? (int) $masterId : $postId;
    }

    /**
     * @inheritDoc
     */
    public function applyListFilter(array $queryArgs): array
    {
        //====================================================================//
        // Never filter on Polylang current language, translations are excluded below
        $queryArgs['lang'] = '';
        //====================================================================//
        // Exclude Translations of a Default Language Post
        $translationsIds = $this->getTranslationsIds((array) ($queryArgs['post_type'] ?? array()));
        if (!empty($translationsIds)) {
            $queryArgs['post__not_in'] = array_merge(
                (array) ($queryArgs['post__not_in'] ?? array()),
                $translationsIds
            );
        }

        return $queryArgs;
    }

    /**
     * @inheritDoc
     */
    public function onPostCreated(int $postId): void
    {
        //====================================================================//
        // Posts created by Splash are in default language
        $defaultSlug = pll_default_language();
        if (is_string($defaultSlug) && !pll_get_post_language($postId)) {
            pll_set_post_language($postId, $defaultSlug);
        }
    }

    /**
     * Get IDs of Posts that are Translations of a Default Language Post
     *
     * Posts in another language without default language translation
     * are their own master: they are not part of this list.
     *
     * @param string[] $postTypes Posts Types to Search
     *
     * @return int[]
     */
    private function getTranslationsIds(array $postTypes): array
    {
        $cacheKey = implode(",", $postTypes);
        if (isset($this->translationsIds[$cacheKey])) {
            return $this->translationsIds[$cacheKey];
        }
        $defaultSlug = pll_default_language();
        $translationsIds = array();
        /** @var string[] $languages */
        $languages = pll_languages_list();
        foreach ($languages as $languageSlug) {
            if ($languageSlug == $defaultSlug) {
                continue;
            }
            /** @var int[] $postIds */
            $postIds = get_posts(array(
                'post_type' => $postTypes,
                'post_status' => 'any',
                'lang' => $languageSlug,
                'fields' => 'ids',
                'posts_per_page' => -1,
                'suppress_filters' => false,
            ));
            foreach ($postIds as $postId) {
                if (!$this->isMaster((int) $postId)) {
                    $translationsIds[] = (int) $postId;
                }
            }
        }

        return $this->translationsIds[$cacheKey] = $translationsIds;
    }
}
