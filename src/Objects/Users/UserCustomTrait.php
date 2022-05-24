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

namespace Splash\Local\Objects\Users;

/**
 * WordPress User Custom Fields Data Access
 */
trait UserCustomTrait
{
    /** @var string */
    private string $userCustomPrefix = "user_custom_";

    /** @var string[] */
    private array $userCustomProtected = array(
        "splash_id", "splash_origin", "first_name", "last_name",
        "billing_first_name", "billing_last_name", "billing_company",
        "billing_address_1", "billing_city", "billing_postcode",
        "billing_country", "billing_state", "billing_phone", "billing_email",
        "shipping_first_name", "shipping_last_name", "shipping_company",
        "shipping_address_1", "shipping_city", "shipping_postcode",
        "shipping_country", "shipping_state", "shipping_phone", "shipping_email",
    );

    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Custom Data Fields using FieldFactory
     *
     * @return void
     */
    private function buildUserCustomFields(): void
    {
        //====================================================================//
        // Require Posts Functions
        require_once(ABSPATH."wp-admin/includes/post.php");

        //====================================================================//
        // Load List of Custom Fields
        $metaKeys = $this->getUserMetaKeys();

        //====================================================================//
        // Filter List of Custom Fields
        foreach ($metaKeys as $index => $metaKey) {
            //====================================================================//
            // Filter Protected Fields
            if (is_protected_meta($metaKey->meta_key)) {
                unset($metaKeys[ $index ]);
            }
            //====================================================================//
            // Filter Splash Fields
            if (in_array($metaKey->meta_key, $this->userCustomProtected, true)) {
                unset($metaKeys[ $index ]);
            }
        }

        //====================================================================//
        // Create Custom Fields Definitions
        foreach ($metaKeys as $metaKey) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier($this->userCustomPrefix.$metaKey->meta_key)
                ->name(ucwords($metaKey->meta_key))
                ->group("User Custom")
                ->microData("http://meta.schema.org/additionalType", $metaKey->meta_key)
                ->isNotTested()
            ;
            //====================================================================//
            // Filter Products Attributes Fields
            if (false !== strpos($metaKey->meta_key, "attribute_pa")) {
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
    private function getUserCustomFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->userCustomPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->userCustomPrefix));
        //====================================================================//
        // Read Field Data
        $data = get_user_meta($this->object->ID, $metaFieldName, true);
        $this->out[$fieldName] = is_scalar($data) ? $data : null;

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
    private function setUserCustomFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Filter Field Id
        if (0 !== strpos($fieldName, $this->userCustomPrefix)) {
            return;
        }
        //====================================================================//
        // Decode Field Id
        $metaFieldName = substr($fieldName, strlen($this->userCustomPrefix));
        //====================================================================//
        // Write Field Data
        if (get_user_meta($this->object->ID, $metaFieldName, true) != $fieldData) {
            update_user_meta($this->object->ID, $metaFieldName, $fieldData);
            $this->needUpdate();
        }
        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Private Functions
    //====================================================================//

    /**
     * Returns all unique meta key from user meta database
     *
     * @return array
     */
    private function getUserMetaKeys(): array
    {
        global $wpdb;

        $select = "SELECT distinct {$wpdb->usermeta}.meta_key FROM {$wpdb->usermeta}";

        /** @var array $userMeta */
        $userMeta = $wpdb->get_results($select);

        return $userMeta;
    }
}
