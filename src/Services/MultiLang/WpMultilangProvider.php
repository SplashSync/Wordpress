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
 * WP Multilang Plugin
 *
 * All languages are stored inline in the same post: no duplicates,
 * every post is a master post, values are encoded strings.
 */
class WpMultilangProvider extends AbstractProvider
{
    /**
     * @inheritDoc
     */
    public function getCode(): string
    {
        return MultiLangPlugins::WP_MULTILANG;
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLanguages(): array
    {
        $languages = array();
        foreach (wpm_get_languages() as $language) {
            $languages[] = (string) $language["translation"];
        }

        return $languages;
    }

    /**
     * @inheritDoc
     */
    public function encodeValue(string $rawValue, string $isoCode): ?string
    {
        $langKey = $this->getLanguageKey($isoCode);
        if (null === $langKey) {
            return null;
        }

        return (string) wpm_translate_string($rawValue, $langKey);
    }

    /**
     * @inheritDoc
     */
    public function decodeValue(string $value, string $isoCode, ?string $origin = null): ?string
    {
        $langKey = $this->getLanguageKey($isoCode);
        if (null === $langKey) {
            return (string) $origin;
        }

        return (string) wpm_set_new_value($origin, $value, array(), $langKey);
    }

    /**
     * Get WP Multilang Language Key from Iso Code
     *
     * @param string $isoCode Language Iso Code (Locale)
     *
     * @return null|string
     */
    private function getLanguageKey(string $isoCode): ?string
    {
        foreach (wpm_get_languages() as $langKey => $language) {
            if ($language["locale"] == $isoCode) {
                return (string) $langKey;
            }
        }

        return null;
    }
}
