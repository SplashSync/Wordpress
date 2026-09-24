---
lang: en
permalink: configure/users
title: Users
description: Control the synchronization of your customers billing and shipping addresses.
updated: 2026-09-24
---

The **Users** tab does not drive the accounts themselves — they are always synchronized — but
the **addresses** attached to them.

WooCommerce gives every customer two addresses: a billing one and a shipping one. Splash
exposes them by default as two distinct **Address** objects, in read and write, so that an ERP
can read and correct them.

The two options below exist to close one of them, or both.

![The Users tab of the Splash Sync settings](../assets/img/settings-users.png "Users tab")

### Disable Shipping Addresses Synchronization

> Off by default: shipping addresses are therefore synchronized.

Tick this box to make your customers shipping addresses **completely invisible to Splash**: no
listing, no reading, no change reported.

### Disable Billing Addresses Synchronization

> Off by default: billing addresses are therefore synchronized.

Same thing for the billing side.

> [!WARNING]
> Mind the direction of these two options: they **disable**. An unticked box means
> synchronization is active — the opposite of the Orders tab options, which enable. This is
> deliberate: the plugin's historical behaviour is to synchronize everything, and an update
> must never cut off a synchronization already in place.

### When to use them

**Your shop does not ship.** Services, digital goods, in-store pickup: shipping addresses are
empty or fanciful. Cutting them off keeps worthless records out of your ERP.

**Your ERP owns the addresses.** When addresses are managed elsewhere and flow down to
WooCommerce, you may prefer the site not to expose them at all, rather than arbitrating the
synchronization direction field by field.

**Volume and GDPR.** Fewer addresses exposed means less personal data travelling, and one less
object to list on a high-volume site.

> [!NOTE]
> The email address of a shipping address is always the one of the customer account, and stays
> read-only: WooCommerce stores no shipping-specific version of it. Only the billing address
> owns its own, editable email.
