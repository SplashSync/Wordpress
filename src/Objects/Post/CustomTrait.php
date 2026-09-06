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
        // Load List of Custom Fields
        /** @var string[] $metaKeys */
        $metaKeys = $this->getObjectMetaKeys();

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
            if (\Splash\Local\Dictionary\CustomFields::getLimit() <= count($metaKeys)) {
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

    /**
     * Get Distinct Meta Keys used by this Object's Post Type
     *
     * WordPress core get_meta_keys() scans the whole postmeta table, so every
     * object type was offered every meta of the site: order metas showed up as
     * Product custom fields, and metas with invalid identifiers triggered
     * schema warnings on unrelated objects. Restrict discovery to the metas
     * actually attached to this object's post type ("product" also includes
     * its variations). Objects without a known post type keep the legacy
     * site-wide behaviour.
     *
     * @return string[]
     */
    private function getObjectMetaKeys(): array
    {
        global $wpdb;

        $postType = isset($this->postType) ? (string) $this->postType : "";
        if ("" === $postType) {
            //====================================================================//
            // Legacy Mode => Site Wide Discovery
            require_once(ABSPATH."wp-admin/includes/post.php");

            /** @var string[] $metaKeys */
            $metaKeys = get_meta_keys();

            return $metaKeys;
        }
        $postTypes = array($postType);
        if ("product" === $postType) {
            $postTypes[] = "product_variation";
        }
        /** @var string[] $metaKeys */
        $metaKeys = $wpdb->get_col(sprintf(
            "SELECT DISTINCT pm.meta_key FROM %s pm JOIN %s p ON p.ID = pm.post_id WHERE p.post_type IN ('%s') ORDER BY pm.meta_key",
            $wpdb->postmeta,
            $wpdb->posts,
            implode("','", array_map("esc_sql", $postTypes))
        ));

        return $metaKeys;
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
        /** @var \WC_Order|\WP_Post $object */
        $object = $this->object;
        $postId = is_a($object, "\\WC_Order") ? $object->get_id() : $object->ID;
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
        /** @var \WC_Order|\WP_Post $object */
        $object = $this->object;
        $postId = is_a($object, "\\WC_Order") ? $object->get_id() : $object->ID;
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
