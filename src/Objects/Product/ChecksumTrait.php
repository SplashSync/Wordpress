<?php

/*
 * This file is part of SplashSync Project.
 *
 * Copyright (C) Splash Sync <www.splashsync.com>
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Splash\Local\Objects\Product;

use Splash\Core\SplashCore      as Splash;

use WC_Product;

/**
 * @abstract    Access to Product Identification CheckSum
 * @author      B. Paquier <contact@splashsync.com>
 */
trait ChecksumTrait
{
    use \Splash\Models\Objects\ChecksumTrait;
    
    /**
    *   @abstract     Build Fields using FieldFactory
    */
    private function buildChecksumFields()
    {
        //====================================================================//
        // Product CheckSum
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("md5")
                ->Name("Md5")
                ->Description("Unik Md5 Object Checksum")
                ->Group("Meta")
                ->isListed()
                ->MicroData("http://schema.org/Thing", "identifier")
                ->isReadOnly();
        
        //====================================================================//
        // Product CheckSum Debug String
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->Identifier("md5-debug")
                ->Name("Md5 Debug")
                ->Description("Unik Checksum String fro Debug")
                ->Group("Meta")
                ->isReadOnly();
    }
    
    /**
     *  @abstract     Read requested Field
     *
     *  @param        string    $Key                    Input List Key
     *  @param        string    $FieldName              Field Identifier / Name
     *
     *  @return         none
     */
    private function getChecksumFields($Key, $FieldName)
    {
        //====================================================================//
        // READ Fields
        switch ($FieldName) {
            case 'md5':
                $this->Out[$FieldName]  =   $this->getMd5Checksum();
                break;

            case 'md5-debug':
                $this->Out[$FieldName]  =   $this->getMd5String();
                break;

            default:
                return;
        }
        
        unset($this->In[$Key]);
    }
    
    /**
     * @abstract    Compute Md5 CheckSum from Product & Attributes Objects
     * @param       WC_Product      $Product          Product Object
     * @return      string          $Md5              Unik Checksum
     */
    public function getMd5Checksum($Product = null)
    {
        $Product    = is_null($Product) ? $this->Product : $Product; 
        return self::getMd5ChecksumFromValues(
            $this->getProductBaseTitle($Product),
            $Product->get_sku(),
            $this->getProductAttributesArray($Product)      
        );
    }
    
    /**
     *      @abstract       Compute Md5 String from Product & Attributes Objects
     *      @return         string        $Md5              Unik Checksum
     */
    public function getMd5String()
    {
        return self::getMd5StringFromValues(
            $this->getProductBaseTitle($this->Product),
            $this->Object->sku,
            $this->getProductAttributesArray($this->Product)                
        );
    }
    
    /**
     * @abstract    Get Product Base Title
     * @param       WC_Product  $Product    Wc Product Object
     * @return      string  
     */
    public function getProductBaseTitle($Product)
    {
        //====================================================================//
        // Detect if Product is Variation
        if ($Product->get_parent_id()) {
            $ParentData =   $Product->get_parent_data();
            return $this->extractMultilangValue($ParentData["title"]);
        }
        return  $this->extractMultilangValue( $Product->get_name());
    }
    
    /**
     *      @abstract       Compute Md5 CheckSum from Product Informations
     *      @param          string        $Title            Product Title without Options
     *      @param          string        $Sku              Product Reference
     *      @param          array         $Attributes       Array of Product Attributes ($Code => $Value)
     *      @return         string        $Md5              Unik Checksum
     */
    private static function getMd5ChecksumFromValues($Title, $Sku = null, $Attributes = [])
    {
        $Md5Array  = array_merge_recursive(
            array("title" => $Title, "sku" => $Sku),
            $Attributes
        );
        return self::md5()->fromArray($Md5Array);
    }
    
    /**
     *      @abstract       Compute Md5 String from Product Informations
     *      @param          string        $Title            Product Title without Options
     *      @param          string        $Sku              Product Reference
     *      @param          array         $Attributes       Array of Product Attributes ($Code => $Value)
     *      @return         string        $Md5              Unik Checksum
     */
    private static function getMd5StringFromValues($Title, $Sku = null, $Attributes = [])
    {
        $Md5Array  = array_merge_recursive(
            array("title" => $Title, "sku" => $Sku),
            $Attributes
        );
        return self::md5()->debugFromArray($Md5Array);
    }
}
