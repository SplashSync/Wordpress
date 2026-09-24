---
lang: en
permalink: configure/products
title: Products
description: Product synchronization options, and how custom fields actually work.
updated: 2026-09-24
---

The **Products** tab holds a single option, but it deserves an explanation: the same mechanism
comes back on orders, invoices, posts and pages.

![The Products tab of the Splash Sync settings](../assets/img/settings-products.png "Products tab")

### Custom Fields

> Enabled by default.

WordPress stores a good part of its data as *post meta*: a free-form value attached to a
content, identified by a key. Your plugins create them all the time — a lead time, a supplier
code, a regulatory notice.

When this option is on, Splash exposes those values as extra fields on your products, **in
read and write**. They become mappable from your Splash workspace, exactly like the price or
the stock.

Three rules decide what shows up in the list:

- **Protected keys are skipped.** That is the WordPress convention: any key starting with an
  underscore (`_purchase_price`, `_wc_...`) is considered internal and is never exposed.
- Splash's own technical keys (`splash_id`, `splash_origin`) are skipped too.
- The list is capped at the **first 200 keys** found on your site.

> [!NOTE]
> The list is built from the keys that actually exist in your database, not from one particular
> product. A key used by a single product will therefore show up on every other one, simply
> empty.

> [!TIP]
> When a field you expect does not appear on the Splash side, it is almost always one of those
> three rules: protected key, cap reached, or a key missing from the database because no
> content carries it yet.

##### Should you leave it on?

Yes, most of the time: this is what brings in the data your plugins write, with no development
at all. Turn it off when your site piles up hundreds of technical keys with no business value —
you will lighten the object description exchanged with Splash by as much.
