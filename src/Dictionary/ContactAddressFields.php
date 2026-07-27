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

namespace Splash\Local\Dictionary;

/**
 * Dictionary of Customer Contact Address Fields
 *
 * Maps WooCommerce Customer Address Meta Suffixes to Splash Field Types
 * and schema.org itemprop, for both Billing & Delivery Address Groups.
 *
 * Field IDs are built as "{AddressTypes}_{suffix}" and match WooCommerce
 * User Meta Keys (billing_company, shipping_city, ...).
 */
class ContactAddressFields
{
    /**
     * Contact Address Fields: Meta Suffix => array(Splash Type, ItemProp)
     */
    public const FIELDS = array(
        "company" => array(SPL_T_VARCHAR, "legalName"),
        "first_name" => array(SPL_T_VARCHAR, "familyName"),
        "last_name" => array(SPL_T_VARCHAR, "givenName"),
        "address_1" => array(SPL_T_VARCHAR, "streetAddress"),
        "address_2" => array(SPL_T_VARCHAR, "postOfficeBoxNumber"),
        "postcode" => array(SPL_T_VARCHAR, "postalCode"),
        "city" => array(SPL_T_VARCHAR, "addressLocality"),
        "state" => array(SPL_T_STATE, "addressRegion"),
        "country" => array(SPL_T_COUNTRY, "addressCountry"),
        "phone" => array(SPL_T_PHONE, "telephone"),
        "email" => array(SPL_T_EMAIL, "email"),
    );

    /**
     * Schema.org ItemTypes for each Address Type
     *
     * Https scheme is required to pair with Splash Scopes definitions.
     */
    public const ITEM_TYPES = array(
        AddressTypes::BILLING => "https://schema.org/billingAddress",
        AddressTypes::DELIVERY => "https://schema.org/deliveryAddress",
    );

    /**
     * Field Suffixes not available for an Address Type
     *
     * WooCommerce core has no shipping_email user meta.
     */
    public const EXCLUDED = array(
        AddressTypes::DELIVERY => array("email"),
    );

    /**
     * Short Field Name Prefixes for each Address Type
     */
    public const PREFIXES = array(
        AddressTypes::BILLING => "[B]",
        AddressTypes::DELIVERY => "[S]",
    );
}
