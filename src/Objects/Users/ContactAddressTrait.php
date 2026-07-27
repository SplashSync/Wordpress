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

use Splash\Local\Dictionary\AddressTypes;
use Splash\Local\Dictionary\ContactAddressFields;
use Splash\Local\Local;

/**
 * WooCommerce Customers Billing & Delivery Address Fields (Read & Write)
 *
 * Exposed in parallel of legacy Read-Only Address fields, paired with
 * Splash Scopes via https://schema.org/billingAddress & deliveryAddress.
 */
trait ContactAddressTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Billing Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildBillingAddressFields(): void
    {
        $this->registerContactAddressFields(AddressTypes::BILLING, __("Billing", "woocommerce"));
    }

    /**
     * Build Delivery Address Fields using FieldFactory
     *
     * @return void
     */
    protected function buildDeliveryAddressFields(): void
    {
        $this->registerContactAddressFields(AddressTypes::DELIVERY, __("Shipping", "woocommerce"));
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
    protected function getContactAddressFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Filter Field Id
        if (!self::isContactAddressField($fieldName)) {
            return;
        }
        //====================================================================//
        // Read Field Data
        /** @var false|scalar $metaData */
        $metaData = get_user_meta($this->object->ID, $fieldName, true);
        $this->out[$fieldName] = $metaData;

        unset($this->in[$key]);
    }

    //====================================================================//
    // Fields Writing Functions
    //====================================================================//

    /**
     * Write Given Fields
     *
     * @param string $fieldName Field Identifier / Name
     * @param scalar $fieldData Field Data
     *
     * @return void
     */
    protected function setContactAddressFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // Filter Field Id
        if (!self::isContactAddressField($fieldName)) {
            return;
        }
        //====================================================================//
        // Write Field Data
        $this->setUserMeta($fieldName, $fieldData);

        unset($this->in[$fieldName]);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Register Contact Address Fields for an Address Type
     */
    private function registerContactAddressFields(string $addressType, string $groupName): void
    {
        //====================================================================//
        // Check if WooCommerce is active
        if (!Local::hasWooCommerce()) {
            return;
        }
        //====================================================================//
        // Walk on Contact Address Fields Definitions
        foreach (ContactAddressFields::FIELDS as $suffix => $definition) {
            //====================================================================//
            // Filter Fields without WooCommerce Storage for this Type
            if (in_array($suffix, ContactAddressFields::EXCLUDED[$addressType] ?? array(), true)) {
                continue;
            }
            list($fieldType, $itemProp) = $definition;
            $this->fieldsFactory()->create($fieldType)
                ->identifier($addressType."_".$suffix)
                ->name(sprintf(
                    "%s %s",
                    ContactAddressFields::PREFIXES[$addressType],
                    ucwords(str_replace("_", " ", $suffix))
                ))
                ->group($groupName)
                ->microData(ContactAddressFields::ITEM_TYPES[$addressType], $itemProp)
            ;
        }
    }

    /**
     * Check if Field ID is a Contact Address Field
     */
    private static function isContactAddressField(string $fieldName): bool
    {
        foreach (array_keys(ContactAddressFields::ITEM_TYPES) as $addressType) {
            if (0 !== strpos($fieldName, $addressType."_")) {
                continue;
            }

            return array_key_exists(
                substr($fieldName, strlen($addressType) + 1),
                ContactAddressFields::FIELDS
            );
        }

        return false;
    }
}
