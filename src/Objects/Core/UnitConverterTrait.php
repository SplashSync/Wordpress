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

use Splash\Components\UnitConverter as Units;

/**
 * WordPress Units Converter Trait
 */
trait UnitConverterTrait
{
    use \Splash\Models\Objects\UnitsHelperTrait;

    /**
     * @var array<string, float>
     */
    private static array $wcWeights = array(
        "g" => Units::MASS_GRAM,
        "kg" => Units::MASS_KG,
        "lbs" => Units::MASS_LIVRE,
        "oz" => Units::MASS_OUNCE,
    );

    /**
     * @var array<string, float>
     */
    private static array $wcLength = array(
        "m" => Units::LENGTH_M,
        "cm" => Units::LENGTH_CM,
        "mm" => Units::LENGTH_MM,
        "in" => Units::LENGTH_INCH,
        "yd" => Units::LENGTH_YARD,
    );

    /**
     * Reading of a Post Meta Weight Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getPostMetaWeight(string $fieldName): self
    {
        //====================================================================//
        //  Read Field Data
        /** @var scalar $realData */
        $realData = get_post_meta($this->object->ID, $fieldName, true);
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcWeights[get_option("woocommerce_weight_unit", "kg")];
        //====================================================================//
        //  Normalize Weight
        $this->out[$fieldName] = self::units()->normalizeWeight((float) $realData, $unit);

        return $this;
    }

    /**
     * Common Writing of a Post Meta Weight Value
     *
     * @param string $fieldName Field Identifier / Name
     * @param float  $fieldData Field Data
     *
     * @return self
     */
    protected function setPostMetaWeight(string $fieldName, float $fieldData): self
    {
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcWeights[get_option("woocommerce_weight_unit", "kg")];
        //====================================================================//
        //  Normalize Weight
        $realData = self::units()->convertWeight($fieldData, $unit);
        //====================================================================//
        //  Write Field Data
        /** @var scalar $currentData */
        $currentData = get_post_meta($this->object->ID, $fieldName, true);
        if (abs((float) $currentData - $realData) > 1E-6) {
            update_post_meta($this->object->ID, $fieldName, $realData);
            $this->needUpdate();
        }

        return $this;
    }

    /**
     * Reading of a Post Meta Length Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getPostMetaLength(string $fieldName): self
    {
        //====================================================================//
        //  Read Field Data
        /** @var scalar $realData */
        $realData = get_post_meta($this->object->ID, $fieldName, true);
        //====================================================================//
        //  Read Current Length Unit
        $unit = self::$wcLength[get_option("woocommerce_dimension_unit", "m")];
        //====================================================================//
        //  Normalize Weight
        $this->out[$fieldName] = self::units()->normalizeLength((float) $realData, $unit);

        return $this;
    }

    /**
     * Common Writing of a Post Meta Length Value
     *
     * @param string $fieldName Field Identifier / Name
     * @param float  $fieldData Field Data
     *
     * @return self
     */
    protected function setPostMetaLength(string $fieldName, float $fieldData): self
    {
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcLength[get_option("woocommerce_dimension_unit", "m")];
        //====================================================================//
        //  Normalize Length
        $realData = self::units()->convertLength($fieldData, $unit);
        //====================================================================//
        //  Write Field Data
        /** @var scalar $currentData */
        $currentData = get_post_meta($this->object->ID, $fieldName, true);
        if (abs((float) $currentData - $realData) > 1E-6) {
            update_post_meta($this->object->ID, $fieldName, $realData);
            $this->needUpdate();
        }

        return $this;
    }
}
