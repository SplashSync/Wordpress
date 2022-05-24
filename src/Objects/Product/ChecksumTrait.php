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

namespace Splash\Local\Objects\Product;

use WC_Product;

/**
 * Access to Product Identification CheckSum
 */
trait ChecksumTrait
{
    use \Splash\Models\Objects\ChecksumTrait;

    /**
     * Compute Md5 CheckSum from Product & Attributes Objects
     *
     * @param null|WC_Product $product Product Object
     *
     * @return string Unique Checksum
     */
    public function getMd5Checksum(WC_Product $product = null): string
    {
        $product = is_null($product) ? $this->product : $product;

        return self::getMd5ChecksumFromValues(
            $this->getProductBaseTitle($product),
            $product->get_sku(),
            $this->getProductAttributesArray($product)
        );
    }

    /**
     * Compute Md5 String from Product & Attributes Objects
     *
     * @return string Unique Checksum
     */
    public function getMd5String(): string
    {
        return self::getMd5StringFromValues(
            $this->getProductBaseTitle($this->product),
            $this->object->sku ?? "",
            $this->getProductAttributesArray($this->product)
        );
    }

    /**
     * Get Product Base Title
     *
     * @param WC_Product $wcProduct Wc Product Object
     *
     * @return string
     */
    public function getProductBaseTitle(WC_Product $wcProduct): string
    {
        //====================================================================//
        // Detect if Product is Variation
        if ($wcProduct->get_parent_id() && is_a($wcProduct, "WC_Product_Variation")) {
            $parentData = $wcProduct->get_parent_data();

            return (string) $this->extractMultilangValue($parentData["title"]);
        }

        return  (string) $this->extractMultilangValue($wcProduct->get_name());
    }

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    protected function buildChecksumFields(): void
    {
        //====================================================================//
        // Product CheckSum
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("md5")
            ->name("Md5")
            ->description("Unique Md5 Object Checksum")
            ->group("Meta")
            ->isListed()
            ->microData("http://schema.org/Thing", "identifier")
            ->isReadOnly()
        ;
        //====================================================================//
        // Product CheckSum Debug String
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("md5-debug")
            ->name("Md5 Debug")
            ->description("Unique Checksum String fro Debug")
            ->group("Meta")
            ->isReadOnly()
        ;
    }

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    protected function getChecksumFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'md5':
                $this->out[$fieldName] = $this->getMd5Checksum();

                break;
            case 'md5-debug':
                $this->out[$fieldName] = $this->getMd5String();

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * Compute Md5 CheckSum from Product Information
     *
     * @param string      $title Product Title without Options
     * @param null|string $sku   Product Reference
     * @param array       $attrs Array of Product Attributes ($Code => $Value)
     *
     * @return string Unique Checksum
     */
    private static function getMd5ChecksumFromValues(string $title, string $sku = null, array $attrs = array()): string
    {
        $md5Array = array_merge_recursive(
            array("title" => $title, "sku" => $sku),
            $attrs
        );

        return (string) self::md5()->fromArray($md5Array);
    }

    /**
     * Compute Md5 String from Product Information
     *
     * @param string      $title Product Title without Options
     * @param null|string $sku   Product Reference
     * @param array       $attrs Array of Product Attributes ($Code => $Value)
     *
     * @return string Unique Checksum
     */
    private static function getMd5StringFromValues(string $title, string $sku = null, array $attrs = array()): string
    {
        $md5Array = array_merge_recursive(
            array("title" => $title, "sku" => $sku),
            $attrs
        );

        return (string) self::md5()->debugFromArray($md5Array);
    }
}
