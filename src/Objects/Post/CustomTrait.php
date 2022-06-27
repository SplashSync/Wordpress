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

namespace Splash\Local\Objects\Post;

use Splash\Core\SplashCore as Splash;
use stdClass;

/**
 * WordPress Custom Fields Data Access
 */
trait CustomTrait
{
    /**
     * @var int
     */
    private static int $maxCustomFields = 200;

    /**
     * @var string
     */
    private string $customPrefix = "custom_";

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Custom Data Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCustomFields(): void
    {
        //====================================================================//
        // Check if feature is Enabled
        $shortClass = strtolower(substr((string) strrchr(static::class, "\\"), 1));
        if (!get_option("splash_cf_".$shortClass)) {
            return;
        }
        //====================================================================//
        // Require Posts Functions
        require_once(ABSPATH."wp-admin/includes/post.php");

        //====================================================================//
        // Load List of Custom Fields
        /** @var string[] $metaKeys */
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
            //====================================================================//
            // Limit max Number of Custom Fields
            if (self::$maxCustomFields <= count($metaKeys)) {
                unset($metaKeys[ $index ]);
            }
        }

        //====================================================================//
        // Create Custom Fields Definitions
        foreach ($metaKeys as $key) {
            //====================================================================//
            // Create Custom Fields Definitions
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier($this->customPrefix.$key)
                ->name(ucwords($key))
                ->group("Custom")
                ->microData("http://meta.schema.org/additionalType", $key)
            ;
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
    private function getCustomFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->customPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->customPrefix));
        $postId = is_a($this->object, "\\WC_Order") ? $this->object->get_id() : $this->object->ID;
        //====================================================================//
        // Read Field Data
        /** @var false|scalar|stdClass $metaData */
        $metaData = get_post_meta($postId, $metaFieldName, true);
        if (!is_object($metaData)) {
            $this->out[$fieldName] = $metaData;
        } else {
            try {
                $this->out[$fieldName] = json_encode($metaData, JSON_THROW_ON_ERROR);
            } catch (\Throwable $ex) {
                $this->out[$fieldName] = null;
            }
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param mixed  $fieldData Field Data
     *
     * @return void
     */
    private function setCustomFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->customPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->customPrefix));
        $postId = is_a($this->object, "\\WC_Order") ? $this->object->get_id() : $this->object->ID;
        //====================================================================//
        // Write Field Data
        /** @var false|scalar|stdClass $metaData */
        $metaData = get_post_meta($postId, $metaFieldName, true);
        if (is_object($metaData)) {
            Splash::log()->war("Custom Field is an object... Update Skipped");
        } elseif ($metaData != $fieldData) {
            update_post_meta($postId, $metaFieldName, $fieldData);
            $this->needUpdate();
        }
        unset($this->in[$fieldName]);
    }
}
