<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2020 Splash Sync  <www.splashsync.com>
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 *  For the full copyright and license information, please view the LICENSE
 *  file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use Splash\Local\Core\TaximonyManager;
use Splash\Models\Helpers\InlineHelper;

/**
 * Wordpress Products Categories Access
 */
trait CategoriesTrait
{
    /**
     * @var string
     */
    protected static $prdTaximony = "product_cat";

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Fields using FieldFactory
     *
     * @return void
     */
    private function buildCategoryFields()
    {
        //====================================================================//
        // Categories Slugs
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//        $this->fieldsFactory()->Create(SPL_T_INLINE)
            ->Identifier("categories")
            ->Name(__("Category"))
            ->Description(__("Product categories")." Slugs")
            ->MicroData("http://schema.org/Product", "category")
            ->addChoices(TaximonyManager::getSlugsChoices(self::$prdTaximony))
            ->setPreferNone()
            ->isNotTested()
            ->isReadOnly()
        ;

        //====================================================================//
        // Categories Names
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
//        $this->fieldsFactory()->Create(SPL_T_INLINE)
            ->Identifier("categories_names")
            ->Name(__("Category")." Name")
            ->Description(__("Product categories")." Names")
            ->MicroData("http://schema.org/Product", "categoryName")
            ->addChoices(TaximonyManager::getNamesChoices(self::$prdTaximony))
            ->isNotTested()
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
    private function getCategoryFields($key, $fieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'categories':
                $this->out[$fieldName] = InlineHelper::fromArray(
                    TaximonyManager::getSlugs($this->getBaseProductId(), self::$prdTaximony)
                );

                break;
            case 'categories_names':
                $this->out[$fieldName] = InlineHelper::fromArray(
                    TaximonyManager::getNames($this->getBaseProductId(), self::$prdTaximony)
                );

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setCategoryFields($fieldName, $fieldData)
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case 'categories':
                TaximonyManager::setSlugs(
                    $this->getBaseProductId(),
                    self::$prdTaximony,
                    InlineHelper::toArray($fieldData)
                );

                break;
            case 'categories_names':
                TaximonyManager::setNames(
                    $this->getBaseProductId(),
                    self::$prdTaximony,
                    InlineHelper::toArray($fieldData)
                );

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
