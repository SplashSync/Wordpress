---
lang: en
permalink: overview
title: Splash for WordPress & WooCommerce
description: Connect your WordPress site and your WooCommerce shop to every other application you run.
updated: 2026-09-24
---

Your shop is not an island. Your stock also lives in your ERP, your customers in your CRM, your
invoices in your accounting software — and every one of those copies drifts a little further
apart each day. Re-keying the same data is slow, and what it really costs is the mistakes:
a price updated in one place only, an order invoiced twice, a stock that says 3 when the
shelf is empty.

The **Splash Connector** puts an end to it. Install it, connect it to your Splash account, and
your WordPress site becomes one more application in a network where data flows on its own. No
development, no scheduled scripts, no export files to babysit. :rocket:

### What gets synchronized

| Object | What it covers | Requires WooCommerce |
|---|---|---|
| **Customers** | Accounts, contact details, billing & shipping addresses | yes |
| **Addresses** | Customers addresses, and the addresses of each order | yes |
| **Products** | Catalogue, variations, prices, stocks, images, barcodes, cost of goods | yes |
| **Orders** | Order lines, statuses, totals, shipping & fees | yes |
| **Invoices** | Invoices, payment dates, amounts | yes |
| **Posts & Pages** | Contents, excerpts, media, publication status | no |

On a bare WordPress, Splash already handles your posts and pages. Everything else unlocks the
moment WooCommerce is active.

### Why it is different from a point-to-point plugin

Most connectors wire one shop to one software. Add a third application and you need a third
plugin, with its own settings, its own bugs and its own release cycle.

Splash works the other way around: every application speaks to a **single hub**, in a shared
data language. Connect WooCommerce once, and it can exchange with Dolibarr, PrestaShop,
Magento, Sylius, a CRM or a marketing tool — today, and with whatever you add next year,
without touching this plugin again.

### You decide what travels, and in which direction

Synchronization is never all-or-nothing. From your Splash workspace you choose, **field by
field**, what leaves your site and what enters it.

- Let your ERP own the prices, while WooCommerce keeps the descriptions and the photos.
- Push your stocks out, and refuse any incoming change.
- Accept new customers from your CRM, but never let it overwrite an existing account.

The plugin also knows how to keep quiet: turn off your customers addresses, your orders
custom fields or your pages in one click if they have no business leaving WordPress.

### What it changes day to day

**Stocks that stay honest.** A sale on the shop, a delivery received in the ERP, a return
recorded at the counter: every movement lands everywhere within seconds. Overselling stops
being a fact of life. :package:

**Invoicing without re-keying.** Orders and invoices reach your accounting software with their
lines, their taxes, their payment dates and their addresses. Your accountant stops waiting for
a monthly export.

**One customer, one record.** The same person buys on your shop, calls your support and gets
billed by your ERP. The Splash Linker merges those profiles into a single entity, so an
address fixed once is fixed everywhere.

**Several sites, one catalogue.** Run two shops, or a shop and a marketplace? Publish once,
let Splash mirror the catalogue, and keep the stocks aligned between them.

### Built for real shops

The plugin is tested against every supported WordPress version, from **6.3 to 7.0**, on PHP
7.4 to 8.3, with the WooCommerce release that matches each one. It follows WordPress
capabilities, so the account you give Splash can only do what you allow it to.

It also knows the plugins you already run — WooCommerce Bookings, Dokan, WPML, WP Multilang,
Wholesale Prices — and adapts what it exposes accordingly.

### Ready in a few minutes

Install the plugin, paste the two keys from your Splash account, check that the self-tests are
green. That is the whole setup. :sparkles:

This plugin is part of the [SplashSync](https://www.splashsync.com) project, and is developed
in the open on [GitHub](https://github.com/SplashSync/Wordpress). Pull requests are welcome.
