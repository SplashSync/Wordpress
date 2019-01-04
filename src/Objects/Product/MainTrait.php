<?php
/*
 * Copyright (C) 2017   Splash Sync       <contact@splashsync.com>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
*/

namespace Splash\Local\Objects\Product;

/**
 * @abstract    Wordpress Core Data Access
 */
trait MainTrait
{

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
    *   @abstract     Build Main Fields using FieldFactory
    */
    private function buildMainFields()
    {

        //====================================================================//
        // Reference
        $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier("_sku")
                ->Name(__("SKU"))
                ->Description(__("Product") . " : " . __("SKU"))
                ->isListed()
                ->MicroData("http://schema.org/Product", "model")
                ->isRequired();

        //====================================================================//
        // Active => Product Is Visible in Catalog
        $this->fieldsFactory()->Create(SPL_T_BOOL)
                ->Identifier("is_visible")
                ->Name(__("Enabled"))
                ->Description(__("Product") . " : " . __("Enabled"))
                ->MicroData("http://schema.org/Product", "offered");

        //====================================================================//
        // PRODUCT SPECIFICATIONS
        //====================================================================//

        $GroupName  = __("Shipping");

        //====================================================================//
        // Weight
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_weight")
                ->Name(__("Weight"))
                ->Description(__("Product") . " " . __("Weight"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product", "weight");

        //====================================================================//
        // Height
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_height")
                ->Name(__("Height"))
                ->Description(__("Product") . " " . __("Height"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product", "height");

        //====================================================================//
        // Depth
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_length")
                ->Name(__("Length"))
                ->Description(__("Product") . " " . __("Length"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product", "depth");

        //====================================================================//
        // Width
        $this->fieldsFactory()->Create(SPL_T_DOUBLE)
                ->Identifier("_width")
                ->Name(__("Width"))
                ->Description(__("Product") . " " . __("Width"))
                ->Group($GroupName)
                ->MicroData("http://schema.org/Product", "width");
    }

    //====================================================================//
    // Fields Reading Functions
    //====================================================================//

    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return       void
     */
    private function getMainFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case '_sku':
            case '_weight':
            case '_length':
            case '_width':
            case '_height':
                $this->getPostMeta($FieldName);
                break;

            case 'is_visible':
                $this->out[$FieldName] = ($this->object->post_status !== "private");
                break;

            default:
                return;
        }

        unset($this->in[$Key]);
    }

    //====================================================================//
    // Fields Writting Functions
    //====================================================================//

    /**
     *  @abstract     Write Given Fields
     *
     *  @param        string    $FieldName              Field Identifier / Name
     *  @param        mixed     $Data                   Field Data
     *
     *  @return       void
     */
    private function setMainFields($FieldName, $Data)
    {
        //====================================================================//
        // WRITE Field
        switch ($FieldName) {
            case '_sku':
            case '_weight':
            case '_length':
            case '_width':
            case '_height':
                $this->setPostMeta($FieldName, $Data);
                break;

            case 'is_visible':
                $this->setSimple("post_status", $Data ? "publish" : "private");
                break;

            default:
                return;
        }

        unset($this->in[$FieldName]);
    }
}
