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

namespace Splash\Local\Objects\Address;

use WP_User;

/**
 * WordPress Address Computed Fields
 *
 * Read-Only fields built from other Address values: contact full name
 * and complete street address.
 */
trait ComputedTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Computed Fields using FieldFactory
     *
     * @return void
     */
    protected function buildComputedFields(): void
    {
        //====================================================================//
        // Contact Full Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("fullname")
            ->name(__("Contact Name", "splash-wordpress-plugin"))
            ->description(__("First Name")." + ".__("Last Name"))
            ->microData("http://schema.org/PostalAddress", "alternateName")
            ->isReadOnly()
        ;
        //====================================================================//
        // Address Full
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("address_full")
            ->name(__("Address line 1 & 2"))
            ->isReadOnly()
        ;
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
    protected function getComputedFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check Address Type Is Defined
        if (empty($this->addressType)) {
            return;
        }
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'fullname':
                $this->out[$fieldName] = $this->getJoinedValues('first_name', 'last_name');

                break;
            case 'address_full':
                $this->out[$fieldName] = $this->getJoinedValues('address_1', 'address_2');

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    //====================================================================//
    // Private Methods
    //====================================================================//

    /**
     * Join Two Address Values in a Single String
     */
    private function getJoinedValues(string $firstId, string $secondId): string
    {
        return trim(sprintf(
            "%s %s",
            $this->getAddressValue($firstId),
            $this->getAddressValue($secondId)
        ));
    }

    /**
     * Read an Address Raw Value, from Wp User Meta or Wc Order Address
     */
    private function getAddressValue(string $fieldId): string
    {
        //====================================================================//
        // From Wp User
        if ($this->object instanceof WP_User) {
            /** @var mixed $metaData */
            $metaData = get_user_meta($this->object->ID, $this->encodeFieldId($fieldId), true);

            return is_scalar($metaData) ? (string) $metaData : "";
        }
        //====================================================================//
        // From Wc Order
        $address = $this->object->get_address($this->getOrderAddressSide());

        return (string) ($address[$fieldId] ?? "");
    }
}
