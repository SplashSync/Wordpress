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

use Splash\Local\Core\CustomFieldsManager;

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
        "billing_address_1", "billing_address_2", "billing_city", "billing_postcode",
        "billing_country", "billing_state", "billing_phone", "billing_email",
        "shipping_first_name", "shipping_last_name", "shipping_company",
        "shipping_address_1", "shipping_address_2", "shipping_city", "shipping_postcode",
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
        // Load List of Custom Fields, Filtered by Site Selection
        $metaKeys = CustomFieldsManager::select(
            $this->getUserMetaKeys(),
            CustomFieldsManager::getRules(CustomFieldsManager::USER)
        );

        //====================================================================//
        // Filter List of Custom Fields
        foreach ($metaKeys as $index => $metaKey) {
            //====================================================================//
            // Filter Protected Fields
            if (is_protected_meta($metaKey)) {
                unset($metaKeys[ $index ]);
            }
            //====================================================================//
            // Filter Splash Fields
            if (in_array($metaKey, $this->userCustomProtected, true)) {
                unset($metaKeys[ $index ]);
            }
        }

        //====================================================================//
        // Create Custom Fields Definitions
        foreach ($metaKeys as $metaKey) {
            $this->fieldsFactory()->create(SPL_T_VARCHAR)
                ->identifier($this->userCustomPrefix.$metaKey)
                ->name(ucwords($metaKey))
                ->group("User Custom")
                ->microData("http://meta.schema.org/additionalType", $metaKey)
                ->isNotTested()
            ;
            //====================================================================//
            // Filter Products Attributes Fields
            if (false !== strpos($metaKey, "attribute_pa")) {
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
     * @return string[]
     */
    private function getUserMetaKeys(): array
    {
        global $wpdb;

        $select = "SELECT distinct {$wpdb->usermeta}.meta_key FROM {$wpdb->usermeta}";

        /** @var string[] $userMeta */
        $userMeta = $wpdb->get_col($select);

        return $userMeta;
    }
}
