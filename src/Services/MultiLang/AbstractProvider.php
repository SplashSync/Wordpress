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

use Splash\Local\Interfaces\MultiLangProviderInterface;
use WP_Post;

/**
 * Base Multi-lang Provider: mono-lang behaviors
 *
 * Every post is a master post, values are stored as is.
 */
abstract class AbstractProvider implements MultiLangProviderInterface
{
    /**
     * @inheritDoc
     */
    public function getDefaultLanguage(): string
    {
        return get_locale();
    }

    /**
     * @inheritDoc
     */
    public function getAvailableLanguages(): array
    {
        return array($this->getDefaultLanguage());
    }

    /**
     * @inheritDoc
     */
    public function isWritableLanguage(string $isoCode): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getMasterId(int $postId): int
    {
        return $postId;
    }

    /**
     * @inheritDoc
     */
    public function isMaster(int $postId): bool
    {
        return $this->getMasterId($postId) == $postId;
    }

    /**
     * @inheritDoc
     */
    public function applyListFilter(array $queryArgs): array
    {
        return $queryArgs;
    }

    /**
     * @inheritDoc
     */
    public function onPostCreated(int $postId): void
    {
    }

    /**
     * @inheritDoc
     */
    public function encodeValue(string $rawValue, string $isoCode): ?string
    {
        return $rawValue;
    }

    /**
     * @inheritDoc
     */
    public function readPostValue(WP_Post $subject, string $fieldName, string $isoCode): ?string
    {
        return $this->encodeValue((string) ($subject->{$fieldName} ?? ""), $isoCode);
    }

    /**
     * @inheritDoc
     */
    public function decodeValue(string $value, string $isoCode, ?string $origin = null): ?string
    {
        return $value;
    }
}
