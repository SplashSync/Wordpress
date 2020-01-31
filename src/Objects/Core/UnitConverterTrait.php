<?php

/*
 *  This file is part of SplashSync Project.
 *
 *  Copyright (C) 2015-2019 Splash Sync  <www.splashsync.com>
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
 * Wordpress Units Converter Trait
 */
trait UnitConverterTrait
{
    use \Splash\Models\Objects\UnitsHelperTrait;

    /**
     * @var array
     */
    private static $wcWeights = array(
        "g" => Units::MASS_GRAM,
        "kg" => Units::MASS_KG,
        "lbs" => Units::MASS_LIVRE,
        "oz" => Units::MASS_OUNCE,
    );

    /**
     * @var array
     */
    private static $wcLength = array(
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
    protected function getPostMetaWheight($fieldName)
    {
        //====================================================================//
        //  Read Field Data
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
     * @param string       $fieldName Field Identifier / Name
     * @param float|string $fieldData Field Data
     *
     * @return self
     */
    protected function setPostMetaWheight($fieldName, $fieldData)
    {
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcWeights[get_option("woocommerce_weight_unit", "kg")];
        //====================================================================//
        //  Normalize Weight
        $realData = self::units()->convertWeight((float) $fieldData, $unit);
        //====================================================================//
        //  Write Field Data
        if (abs((float) get_post_meta($this->object->ID, $fieldName, true) - $realData) > 1E-6) {
            update_post_meta($this->object->ID, $fieldName, $realData);
            $this->needUpdate();
        }

        return $this;
    }

    /**
     * Reading of a Post Meta Lenght Value
     *
     * @param string $fieldName Field Identifier / Name
     *
     * @return self
     */
    protected function getPostMetaLenght($fieldName)
    {
        //====================================================================//
        //  Read Field Data
        $realData = get_post_meta($this->object->ID, $fieldName, true);
        //====================================================================//
        //  Read Current Lenght Unit
        $unit = self::$wcLength[get_option("woocommerce_dimension_unit", "m")];
        //====================================================================//
        //  Normalize Weight
        $this->out[$fieldName] = self::units()->normalizeLength((float) $realData, $unit);

        return $this;
    }

    /**
     * Common Writing of a Post Meta Lenght Value
     *
     * @param string       $fieldName Field Identifier / Name
     * @param float|string $fieldData Field Data
     *
     * @return self
     */
    protected function setPostMetaLenght($fieldName, $fieldData)
    {
        //====================================================================//
        //  Read Current Weight Unit
        $unit = self::$wcLength[get_option("woocommerce_dimension_unit", "m")];
        //====================================================================//
        //  Normalize Lenght
        $realData = self::units()->convertLength((float) $fieldData, $unit);
        //====================================================================//
        //  Write Field Data
        if (abs((float) get_post_meta($this->object->ID, $fieldName, true) - $realData) > 1E-6) {
            update_post_meta($this->object->ID, $fieldName, $realData);
            $this->needUpdate();
        }

        return $this;
    }
}
