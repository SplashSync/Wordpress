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

use ArrayObject;
use Splash\Local\Services\MultiLang\MultiLangManager;

/**
 * WordPress Multi-lang Data Access
 *
 * Thin facade over the active Multi-lang Provider (see MultiLangManager),
 * kept for Objects Fields readers & writers.
 */
trait MultiLangTrait
{
    /**
     * Get Default Language Iso Code
     *
     * @return string
     */
    public static function getDefaultLanguage(): string
    {
        return MultiLangManager::getProvider()->getDefaultLanguage();
    }

    /**
     * Check if Language is Default Language
     *
     * @param string $isoCode Language Iso Code
     *
     * @return bool
     */
    public static function isDefaultLanguage(string $isoCode): bool
    {
        return (self::getDefaultLanguage() == $isoCode);
    }

    /**
     * Get ID of the Master Post: the post in default language
     *
     * @param int $postId Wp Post ID
     *
     * @return int
     */
    public static function getMultiLangMaster(int $postId): int
    {
        return MultiLangManager::getProvider()->getMasterId($postId);
    }

    /**
     * Check if Post is in Master Language
     *
     * @param int $postId Wp Post ID
     *
     * @return bool
     */
    public static function isMultiLangMaster(int $postId): bool
    {
        return MultiLangManager::getProvider()->isMaster($postId);
    }

    /**
     * Check if Language allow Writing
     *
     * @param string $languageCode Language Code
     *
     * @return bool
     */
    public static function isWritableLanguage(string $languageCode): bool
    {
        return MultiLangManager::getProvider()->isWritableLanguage($languageCode);
    }

    /**
     * Reduce Field Name Based on Language Code
     *
     * @param string $fieldName Origine Field Name
     * @param string $isoCode   Language Iso Code
     *
     * @return null|string
     */
    public static function getMultiLangFieldName(string $fieldName, string $isoCode): ?string
    {
        //====================================================================//
        // Default Language => No ISO Code on Field Name
        if (self::isDefaultLanguage($isoCode)) {
            return $fieldName;
        }
        //====================================================================//
        // Other Languages => Check if IsoCode is Present in FieldName
        if (false === strpos($fieldName, $isoCode)) {
            return null;
        }

        //====================================================================//
        // Other Languages => Remove ISO Code on Field Name
        return substr($fieldName, 0, strlen($fieldName) - strlen($isoCode) - 1) ?: null;
    }

    /**
     * Get Languages exposed on Multi-lang Fields
     *
     * @return string[]
     */
    public static function getAvailableLanguages(): array
    {
        return MultiLangManager::getProvider()->getAvailableLanguages();
    }

    /**
     * Read Multi-lang Field
     *
     * @param string $fieldName Field Name
     * @param string $isoCode   Language Iso Code
     * @param string $object    Property Name
     *
     * @return self
     */
    protected function getMultiLang(string $fieldName, string $isoCode, string $object = "object"): self
    {
        //====================================================================//
        // Safety Check
        if (!isset($this->out)) {
            return $this;
        }
        //====================================================================//
        // Redirect to Multi-lang Index
        $index = self::isDefaultLanguage($isoCode) ? $fieldName : $fieldName."_".$isoCode;
        $this->out[$index] = MultiLangManager::getProvider()->readPostValue(
            $this->{$object},
            $fieldName,
            $isoCode
        );

        return $this;
    }

    /**
     * Write Multi-lang Field
     *
     * @param string $fieldName Field Name
     * @param string $isoCode   Language Iso Code
     * @param string $fieldData Field Data
     * @param string $object    Property Name
     *
     * @return self
     */
    protected function setMultiLang(
        string $fieldName,
        string $isoCode,
        string $fieldData,
        string $object = "object"
    ): self {
        if (method_exists($this, "setSimple")) {
            $this->setSimple(
                $fieldName,
                $this->decodeMultiLang($fieldData, $isoCode, $this->{$object}->{$fieldName}),
                $object
            );
        }

        return $this;
    }

    /**
     * Extract Value for a Language from a Raw Stored Value
     *
     * @param string      $fieldData Raw Stored Value
     * @param null|string $isoCode   Language Iso Code, Default Language if Null
     *
     * @return null|string
     */
    protected static function encodeMultiLang(string $fieldData, ?string $isoCode = null): ?string
    {
        return MultiLangManager::getProvider()->encodeValue(
            $fieldData,
            $isoCode ?? self::getDefaultLanguage()
        );
    }

    /**
     * Merge a Value for a Language into the Raw Stored Value
     *
     * @param string      $fieldData New Value for this Language
     * @param null|string $isoCode   Language Iso Code, Default Language if Null
     * @param null|string $origin    Current Raw Stored Value
     *
     * @return null|string
     */
    protected static function decodeMultiLang(
        string $fieldData,
        ?string $isoCode = null,
        ?string $origin = null
    ): ?string {
        return MultiLangManager::getProvider()->decodeValue(
            $fieldData,
            $isoCode ?? self::getDefaultLanguage(),
            $origin
        );
    }

    /**
     * Get Raw Value for Lists & Checksums
     *
     * Values are kept raw on purpose: changing them changes products checksums.
     *
     * @param string $fieldData Raw Stored Value
     *
     * @return string
     */
    protected function extractMultilangValue(string $fieldData): string
    {
        return $fieldData;
    }

    /**
     * Build Multilang Array from Fields Array
     *
     * @param array|ArrayObject $fieldData Source Data
     * @param string            $fieldName Base Name (Array Key) for Field to Detect
     *
     * @return array
     */
    protected static function buildMultilangArray($fieldData, string $fieldName): array
    {
        //====================================================================//
        // Init Result Array
        $response = array();
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Reduce Multilang Field Name
            $index = self::isDefaultLanguage($isoCode) ? $fieldName : $fieldName."_".$isoCode;
            //====================================================================//
            // Check if Field Value Exists
            if (!isset($fieldData[$index]) || !is_scalar($fieldData[$index])) {
                continue;
            }
            //====================================================================//
            // Add Value To Results
            $response[$isoCode] = $fieldData[$index];
        }

        return $response;
    }

    /**
     * Update field value using Multilang Array
     *
     * @param string $originData Original Source Data (Raw String)
     * @param array  $newData    New Data (Multilang Array)
     *
     * @return string
     */
    protected static function applyMultilangArray(string $originData, array $newData): string
    {
        //====================================================================//
        // Walk on Available Languages
        foreach (self::getAvailableLanguages() as $isoCode) {
            //====================================================================//
            // Check if Field Value Exists
            if (!isset($newData[$isoCode]) || !is_scalar($newData[$isoCode])) {
                continue;
            }
            $originData = (string) self::decodeMultiLang((string) $newData[$isoCode], $isoCode, $originData);
        }

        return $originData;
    }
}
