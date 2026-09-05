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

namespace Splash\Local\Objects\CreditNote;

/**
 * WooCommerce Credit Note Core Data Access
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
    protected function buildCoreFields(): void
    {
        //====================================================================//
        // Source Invoice
        //
        // This is the field the whole object exists for. Dolibarr stores it as
        // `fk_facture_source` on the credit note; WooCommerce stores it as the
        // refund's post_parent. Mapping them makes a credit note land on the
        // right invoice instead of floating free.
        $this->fieldsFactory()->create((string) self::objects()->encode("Invoice", SPL_T_ID))
            ->identifier("parent_id")
            ->name(__("Invoice"))
            ->microData("http://schema.org/Invoice", "referencesOrder")
            ->isReadOnly()
            ->isRequired()
        ;
        //====================================================================//
        // Source Order
        //
        // Same identifier, seen as an Order rather than an Invoice, so a remote
        // server that syncs Orders but not Invoices still gets the link.
        $this->fieldsFactory()->create((string) self::objects()->encode("Order", SPL_T_ID))
            ->identifier("parent_order_id")
            ->name(__("Order"))
            ->microData("http://schema.org/Order", "orderNumber")
            ->isReadOnly()
        ;
        //====================================================================//
        // Customer Object
        //
        // Read from the parent order: a refund has no customer of its own.
        $this->fieldsFactory()->create((string) self::objects()->encode("ThirdParty", SPL_T_ID))
            ->identifier("_customer_id")
            ->name(__("Customer"))
            ->microData("http://schema.org/Invoice", "customer")
            ->isReadOnly()
        ;
        //====================================================================//
        // Reference
        //
        // Prefer the credit note number issued by YITH WooCommerce PDF Invoices
        // when the plugin is in use, so the reference matches the document the
        // customer actually received. Fall back to a derived reference otherwise.
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("reference")
            ->name(__("Reference"))
            ->microData("http://schema.org/Invoice", "confirmationNumber")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Credit Note Date
        $this->fieldsFactory()->create(SPL_T_DATE)
            ->identifier("_date_created")
            ->name(__("Date"))
            ->microData("http://schema.org/Invoice", "paymentDueDate")
            ->isReadOnly()
            ->isRequired()
        ;
        //====================================================================//
        // Credit Note Created DateTime
        $this->fieldsFactory()->create(SPL_T_DATETIME)
            ->identifier("_datetime_created")
            ->name(__("Creation DateTime"))
            ->microData("http://schema.org/DataFeedItem", "dateCreated")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Refund Reason
        //
        // Free text typed by whoever issued the refund. This is what should end
        // up in the credit note description on the remote server.
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("reason")
            ->name(__("Reason for refund"))
            ->microData("http://schema.org/Invoice", "description")
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Is the Source Invoice now Fully Credited
        //
        // This is what an accounting system actually needs: whether the invoice
        // can be closed or still carries a balance.
        //
        // Deliberately NOT read from the `_refund_type` meta. That meta is written
        // by WooCommerce Analytics (Admin\API\Reports\Products\DataStore) on the
        // `woocommerce_order_(partially|fully)_refunded` hooks, so it records the
        // state the ORDER reached when this refund landed — not this refund's own
        // coverage — and it is never revised afterwards. Deleting a sibling refund
        // or editing the order leaves it stale: on a real shop, 165 of 399 refunds
        // are flagged "full" while covering only part of their order. It is
        // recomputed live from the parent instead.
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("is_order_fully_refunded")
            ->name(__("Invoice fully credited"))
            ->description(__("Computed live from the parent order, all refunds included"))
            ->isReadOnly()
            ->isListed()
        ;
        //====================================================================//
        // Total Refunded on the Source Invoice
        //
        // The cumulative amount credited on the parent, this refund included. A
        // remote server needs it to reconcile several credit notes against one
        // invoice: 17 orders on a real shop carry more than one refund.
        $this->fieldsFactory()->create(SPL_T_DOUBLE)
            ->identifier("order_total_refunded")
            ->name(__("Total refunded on invoice"))
            ->isReadOnly()
        ;
        //====================================================================//
        // Refunded via Payment Gateway
        //
        // True when WooCommerce actually asked the gateway to move the money,
        // false when the refund was only recorded in the shop. A remote
        // accounting system needs the difference: only the first one has a
        // matching bank movement.
        $this->fieldsFactory()->create(SPL_T_BOOL)
            ->identifier("refunded_payment")
            ->name(__("Refunded via payment gateway"))
            ->isReadOnly()
        ;
        //====================================================================//
        // Refunded By
        //
        // The WordPress user who issued the refund. Kept as a plain name rather
        // than a ThirdParty id: this is shop staff, not a customer.
        $this->fieldsFactory()->create(SPL_T_VARCHAR)
            ->identifier("refunded_by")
            ->name(__("Refunded by"))
            ->microData("http://schema.org/Author", "name")
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
            case 'parent_id':
                $parentId = $this->object->get_parent_id();
                $this->out[$fieldName] = $parentId
                    ? self::objects()->encode("Invoice", (string) $parentId)
                    : null;

                break;
            case 'parent_order_id':
                $parentId = $this->object->get_parent_id();
                $this->out[$fieldName] = $parentId
                    ? self::objects()->encode("Order", (string) $parentId)
                    : null;

                break;
            case '_customer_id':
                $parent = $this->getParentOrder($this->object);
                $customerId = $parent ? $parent->get_customer_id() : 0;
                $this->out[$fieldName] = $customerId
                    ? self::objects()->encode("ThirdParty", (string) $customerId)
                    : null;

                break;
            case 'reference':
                $this->out[$fieldName] = $this->getCreditNoteReference();

                break;
            case '_date_created':
                $date = $this->object->get_date_created();
                $this->out[$fieldName] = $date ? $date->format(SPL_T_DATECAST) : null;

                break;
            case '_datetime_created':
                $date = $this->object->get_date_created();
                $this->out[$fieldName] = $date ? $date->format(SPL_T_DATETIMECAST) : null;

                break;
            case 'reason':
                $this->out[$fieldName] = (string) $this->object->get_reason();

                break;
            case 'is_order_fully_refunded':
                $this->out[$fieldName] = $this->isOrderFullyRefunded();

                break;
            case 'order_total_refunded':
                $parent = $this->getParentOrder($this->object);
                $this->out[$fieldName] = $parent ? (float) $parent->get_total_refunded() : 0.0;

                break;
            case 'refunded_payment':
                $this->out[$fieldName] = (bool) $this->object->get_refunded_payment();

                break;
            case 'refunded_by':
                $this->out[$fieldName] = $this->getRefundAuthorName();

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
    // Private Helpers
    //====================================================================//

    /**
     * Build the Credit Note Reference
     *
     * @return string
     */
    private function getCreditNoteReference(): string
    {
        //====================================================================//
        // YITH WooCommerce PDF Invoice issues a real credit note number. When it
        // is there, it is the reference the customer has on their document, so
        // it wins over anything derived.
        $ywpiNumber = (string) $this->object->get_meta('_ywpi_credit_note_formatted_number');
        if (!empty($ywpiNumber)) {
            return $ywpiNumber;
        }

        //====================================================================//
        // Otherwise, derive a stable reference from the parent order, so a credit
        // note is still recognisable next to the invoice it belongs to.
        $parent = $this->getParentOrder($this->object);
        $parentRef = $parent ? $parent->get_order_number() : (string) $this->object->get_parent_id();

        return sprintf("#%s-R%d", $parentRef, $this->object->get_id());
    }

    /**
     * Tell whether the Source Invoice is now Fully Credited
     *
     * Computed from the parent order's cumulative refunds, so it stays true after
     * a sibling refund is deleted or the order is edited.
     *
     * @return bool
     */
    private function isOrderFullyRefunded(): bool
    {
        $parent = $this->getParentOrder($this->object);
        if (!$parent) {
            return false;
        }
        $total = (float) $parent->get_total();
        if ($total <= 0.0) {
            return false;
        }

        //====================================================================//
        // Compare on a one cent tolerance: rounding on split refunds otherwise
        // leaves an invoice one cent short of closed.
        return ((float) $parent->get_total_refunded() + 0.01) >= $total;
    }

    /**
     * Get the Display Name of the User who issued the Refund
     *
     * @return string
     */
    private function getRefundAuthorName(): string
    {
        $userId = (int) $this->object->get_refunded_by();
        if (empty($userId)) {
            return "";
        }
        $user = get_userdata($userId);

        return $user ? (string) $user->display_name : sprintf("#%d", $userId);
    }
}
