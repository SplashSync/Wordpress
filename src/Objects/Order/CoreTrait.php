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

use Splash\Client\Splash;

/**
 * WooCommerce Order Core Data Access
 */
trait CoreTrait
{
    //====================================================================//
    // Fields Generation Functions
    //====================================================================//

    /**
     * Build Core Fields using FieldFactory
     *
     * @return void
     */
    protected function buildCoreFields()
    {
        //====================================================================//
        // Customer Object
        $this->fieldsFactory()->create((string) self::objects()->Encode("ThirdParty", SPL_T_ID))
            ->identifier("_customer_id")
            ->name(__("Customer"))
            ->isReadOnly(!Splash::isTravisMode())
            ->isRequired()
        ;
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->microData("http://schema.org/Invoice", "customer");
        } else {
            $this->fieldsFactory()
                ->microData("http://schema.org/Organization", "ID");
        }
        //====================================================================//
        // Reference
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("reference")
            ->name(__("Reference"))
            ->microData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
            ->isListed()
        ;
        if (is_a($this, "\\Splash\\Local\\Objects\\Invoice")) {
            $this->fieldsFactory()
                ->microData("http://schema.org/Invoice", "confirmationNumber");
        } else {
            $this->fieldsFactory()
                ->microData("http://schema.org/Order", "orderNumber");
        }
        //====================================================================//
        // Order Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->identifier("_date_created")
            ->name(__("Order date"))
            ->microData("http://schema.org/Order", "orderDate")
            ->isReadOnly(!Splash::isTravisMode())
            ->isRequired()
        ;
        //====================================================================//
        // Order Created DateTime
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("_datetime_created")
            ->name(__("Creation DateTime"))
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly()
        ;
        //====================================================================//
        // Wordpress Blog Name
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("blogname")
            ->name("Blog Name")
            ->microData("http://schema.org/Author", "alternateName")
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
    protected function getCoreFields(string $key, string $fieldName): void
    {
        //====================================================================//
        // READ Fields
        switch ($fieldName) {
            case '_customer_id':
                if (!$this->object->get_customer_id()) {
                    $this->out[$fieldName] = null;

                    break;
                }
                $this->out[$fieldName] = self::objects()
                    ->encode("ThirdParty", (string) $this->object->get_customer_id())
                ;

                break;
            case 'reference':
                $this->out[$fieldName] = "#".$this->object->get_order_number();

                break;
            case '_date_created':
                $orderDate = $this->object->get_date_created();
                $this->out[$fieldName] = is_null($orderDate) ? null : $orderDate->format(SPL_T_DATECAST);

                break;
            case '_datetime_created':
                $orderDate = $this->object->get_date_created();
                $this->out[$fieldName] = is_null($orderDate) ? null : $orderDate->format(SPL_T_DATETIMECAST);

                break;
            case 'blogname':
                /** @var null|string $blogName */
                $blogName = get_option("blogname", "WordPress");
                $this->out[$fieldName] = $blogName ?? "WordPress";

                break;
            default:
                return;
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
     * @param scalar $fieldData Field Data
     *
     * @return void
     */
    protected function setCoreFields(string $fieldName, $fieldData): void
    {
        //====================================================================//
        // WRITE Field
        switch ($fieldName) {
            case '_customer_id':
                $this->setGeneric($fieldName, self::objects()->id((string) $fieldData));

                break;
            case '_date_created':
                $this->setGeneric($fieldName, (string) $fieldData);

                break;
            default:
                return;
        }

        unset($this->in[$fieldName]);
    }
}
