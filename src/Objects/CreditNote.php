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

namespace Splash\Local\Objects;

use Splash\Models\AbstractObject;
use Splash\Models\Objects;
use WC_Order_Refund;

/**
 * WooCommerce Credit Note Object
 *
 * Unlike Invoice, which is a virtual read-only view of an Order, a Credit Note is
 * backed by a real WooCommerce entity: the `shop_order_refund` post type, handled
 * by the WC_Order_Refund class.
 *
 * This matters for the Dolibarr connector, whose CreditNote object carries a
 * `fk_facture_source` pointing back at the invoice being credited. WooCommerce has
 * the exact same link natively, as the refund's `post_parent`, so the two sides can
 * be mapped without inventing anything.
 *
 * SIGN CONVENTION
 * The Dolibarr connector needs a dedicated `Core\CreditModeTrait` to invert every
 * price, because a Dolibarr credit note stores positive amounts. WooCommerce does
 * not: `get_total()` already returns a negative value (`get_amount()` is the
 * positive one). No inversion trait is needed here — but do not mix the two getters.
 *
 * SCOPE OF THIS OBJECT
 * Read-only for now, exactly like Invoice. Creating a refund from a remote server
 * is a money-moving operation — it can trigger a gateway refund — so it is left out
 * until the write path has been designed explicitly.
 */
class CreditNote extends AbstractObject
{
    //====================================================================//
    // Splash Php Core Traits
    //====================================================================//

    use Objects\IntelParserTrait;
    use Objects\SimpleFieldsTrait;
    use Objects\GenericFieldsTrait;
    use Objects\PricesTrait;
    use Objects\ListsTrait;

    //====================================================================//
    // Core Fields
    //====================================================================//

    use Core\WooCommerceObjectTrait;        // Trigger WooCommerce Module Activation

    //====================================================================//
    // WooCommerce Credit Note Fields
    //====================================================================//

    use CreditNote\CRUDTrait;               // Objects CRUD
    use CreditNote\ObjectListTrait;         // Objects Listing
    use CreditNote\HooksTrait;              // WordPress Hooks
    use CreditNote\CoreTrait;               // Credit Note Core Infos
    use CreditNote\ItemsTrait;              // Credit Note Items List
    use CreditNote\TotalsTrait;             // Credit Note Totals

    //====================================================================//
    // Object Definition Parameters
    //====================================================================//

    /**
     * Object Name (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $name = "Credit Note";

    /**
     * Object Description (Translated by Module)
     *
     * {@inheritdoc}
     */
    protected static string $description = "WooCommerce Order Refund";

    /**
     * Object Icon (FontAwesome or Glyph ico tag)
     *
     * {@inheritdoc}
     */
    protected static string $ico = "fa fa-reply";

    //====================================================================//
    // Object Synchronization Limitations
    //
    // This Flags are Used by Splash Server to Prevent Unexpected Operations on Remote Server
    //====================================================================//

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $allowPushDeleted = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushCreated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushUpdated = false;

    /**
     * {@inheritdoc}
     */
    protected static bool $enablePushDeleted = false;

    //====================================================================//
    // General Class Variables
    //====================================================================//

    /**
     * @var WC_Order_Refund
     */
    protected object $object;

    /**
     * @var string
     */
    protected string $postType = "shop_order_refund";

    //====================================================================//
    // Class Constructor
    //====================================================================//

    /**
     * Class Constructor
     */
    public function __construct()
    {
        self::setGenericMethodsFormat("snake_case");
    }
}
