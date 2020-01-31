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

namespace Splash\Local\Objects\Post;

/**
 * Wordpress Custom Fields Data Access
 */
trait CustomTrait
{
    /**
     * @var string
     */
    private $customPrefix = "custom_";

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Custom Data Fields using FieldFactory
     *
     * @return void
     */
    private function buildCustomFields()
    {
        //====================================================================//
        // Require Posts Functions
        require_once(ABSPATH."wp-admin/includes/post.php");

        //====================================================================//
        // Load List of Custom Fields
        $metaKeys = get_meta_keys();

        //====================================================================//
        // Filter List of Custom Fields
        foreach ($metaKeys as $index => $key) {
            //====================================================================//
            // Filter Protected Fields
            if (is_protected_meta($key)) {
                unset($metaKeys[ $index ]);
            }
            //====================================================================//
            // Filter Splash Fields
            if (("splash_id" == $key) || ("splash_origin" == $key)) {
                unset($metaKeys[ $index ]);
            }
        }

        //====================================================================//
        // Create Custom Fields Definitions
        foreach ($metaKeys as $key) {
            $this->fieldsFactory()->Create(SPL_T_VARCHAR)
                ->Identifier($this->customPrefix.$key)
                ->Name(ucwords($key))
                ->Group("Custom")
                ->MicroData("http://meta.schema.org/additionalType", $key);

            //====================================================================//
            // Filter Products Attributes Fields
            if (false !== strpos($key, "attribute_pa")) {
                $this->fieldsFactory()->isReadOnly();
            }
        }
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
    private function getCustomFields($key, $fieldName)
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->customPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->customPrefix));
        //====================================================================//
        // Read Field Data
        $this->out[$fieldName] = get_post_meta($this->object->ID, $metaFieldName, true);

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
    private function setCustomFields($fieldName, $fieldData)
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->customPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->customPrefix));
        //====================================================================//
        // Write Field Data
        if (get_post_meta($this->object->ID, $metaFieldName, true) != $fieldData) {
            update_post_meta($this->object->ID, $metaFieldName, $fieldData);
            $this->needUpdate();
        }
        unset($this->in[$fieldName]);
    }
}
