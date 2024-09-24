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

namespace Splash\Local\Objects\Order;

use Splash\Local\Local;

/**
 * Manage Access to Send Cloud Sipping Information
 */
trait WcSendCloudTrait
{
    /**
     * Send Cloud Order Service Point Metadata Storage Key
     */
    private static string $sendCloudMetaKey = "sendcloudshipping_checkout_payload_meta";

    /**
     * Build SendCould dedicated Fields
     */
    protected function buildWcSendCloudFields(): void
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooSendCloud()) {
            return;
        }

        //====================================================================//
        // SendCloud Service Point Address
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("send_cloud_service_point_address")
            ->name("Service Point Json")
            ->description("SendCloud Service Point as Json String")
            ->group("SendCloud")
            ->microData("https://schema.org/Order", "sendCloudServicePointAddressJson")
            ->isReadOnly()
        ;
        //====================================================================//
        // SendCloud Service Point Required
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("send_cloud_service_point_required")
            ->name("Service Point Required")
            ->description("SendCloud Service Point is Required")
            ->group("SendCloud")
            ->microData("https://schema.org/Order", "sendCloudServicePointRequired")
            ->isReadOnly()
        ;
    }

    /**
     * @param string $key
     * @param string $fieldName
     *
     * @return void
     */
    protected function getWcSendCloudFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooSendCloud()) {
            return;
        }

        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case 'send_cloud_service_point_address':
                //====================================================================//
                // Get Service Point Metadata
                $metaData = $this->getWcSendCloudMetadata();
                if (!$metaData || !is_array($metaData["delivery_method_data"]["service_point"] ?? null)) {
                    $this->out[$fieldName] = null;

                    break;
                }
                //====================================================================//
                // Get Service Point as Json
                $this->out[$fieldName] = json_encode($metaData["delivery_method_data"]["service_point"]);

                break;
            case 'send_cloud_service_point_required':
                //====================================================================//
                // Get Service Point Metadata
                $metaData = $this->getWcSendCloudMetadata();
                if (!$metaData || !is_array($metaData["delivery_method_data"]["service_point"] ?? null)) {
                    $this->out[$fieldName] = false;

                    break;
                }
                //====================================================================//
                // Service Point is Required
                $this->out[$fieldName] = true;

                break;
            default:
                return;
        }

        unset($this->in[$key]);
    }

    /**
     * @param string $key
     * @param string $fieldName
     *
     * @return void
     */
    protected function getWcSendCloudRelayCodeFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (("customer_note" != $fieldName) || !Local::hasWooSendCloud()) {
            return;
        }
        //====================================================================//
        // Fetch SendCloud Metadata for Order
        $metaData = get_post_meta($this->object->ID, self::$sendCloudMetaKey, true);
        if (!is_array($metaData) || empty($metaData)) {
            return;
        }
        //====================================================================//
        // Get SendCloud Relay Code
        $relayCode = $metaData["delivery_method_data"]["service_point"]["code"] ?? null;
        if ($relayCode && is_string($relayCode)) {
            $this->out[$fieldName] = $relayCode;
        }
    }

    /**
     * Retrieve Send Cloud Order Metadata
     */
    private function getWcSendCloudMetadata(): ?array
    {
        //====================================================================//
        // Check if Module is Installed & Active
        if (!Local::hasWooSendCloud()) {
            return null;
        }
        //====================================================================//
        // Fetch SendCloud Metadata for Order
        $metaData = get_post_meta($this->object->ID, self::$sendCloudMetaKey, true);
        if (!is_array($metaData) || empty($metaData)) {
            return null;
        }

        return $metaData;
    }
}
