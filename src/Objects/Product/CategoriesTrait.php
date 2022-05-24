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

use Splash\Local\Core\TaxonomyManager;
use Splash\Models\Helpers\InlineHelper;

/**
 * WordPress Products Categories Access
 */
trait CategoriesTrait
{
    /**
     * @var string
     */
    protected static string $prdTaximony = "product_cat";

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildCategoryFields(): void
    {
        //====================================================================//
        // Categories Slugs
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("categories")
            ->name(__("Category"))
            ->description(__("Product categories")." Slugs")
            ->microData("http://schema.org/Product", "category")
            ->addChoices(TaxonomyManager::getSlugsChoices(self::$prdTaximony))
            ->setPreferNone()
        ;
        //====================================================================//
        // Categories Names
        $this->fieldsFactory()->create(SPL_T_INLINE)
            ->identifier("categories_names")
            ->name(__("Category")." Name")
            ->description(__("Product categories")." Names")
            ->microData("http://schema.org/Product", "categoryName")
            ->addChoices(TaxonomyManager::getNamesChoices(self::$prdTaximony))
            ->setPreferNone()
            ->isNotTested()
            ->isReadOnly()
        ;
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     * Read requested Field
     *
     * @param string $key       Input List Key
     * @param string $fieldName Field Identifier / Name
     *
     * @return void
     */
    private function getCategoryFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'categories':
                $this->out[$fieldName] = InlineHelper::fromArray(
                    TaxonomyManager::getSlugs($this->getBaseProductId(), self::$prdTaximony)
                );

                break;
            case 'categories_names':
                $this->out[$fieldName] = InlineHelper::fromArray(
                    TaxonomyManager::getNames($this->getBaseProductId(), self::$prdTaximony)
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string      $fieldName Field Identifier / Name
     * @param null|scalar $fieldData Field Data
     *
     * @return void
     */
    private function setCategoryFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'categories':
                TaxonomyManager::setSlugs(
                    $this->getBaseProductId(),
                    self::$prdTaximony,
                    InlineHelper::toArray((string) $fieldData)
                );

                break;
            case 'categories_names':
                TaxonomyManager::setNames(
                    $this->getBaseProductId(),
                    self::$prdTaximony,
                    InlineHelper::toArray((string) $fieldData)
                );

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
