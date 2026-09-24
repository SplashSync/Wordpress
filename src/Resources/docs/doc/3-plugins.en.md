---
lang: en
permalink: docs/plugins
title: Compatible Plugins
description: The third-party WordPress plugins this connector knows how to work with.
updated: 2026-09-24
---

Splash detects the plugins below and adapts what it exposes accordingly. None of them is
required: install only the ones you use.

### WooCommerce

The most popular e-commerce plugin for WordPress.
[wordpress.org/plugins/woocommerce](https://wordpress.org/plugins/woocommerce/)

Splash is natively compatible with WooCommerce, and unlocks customers, addresses, products,
orders and invoices as soon as it is active.

### WooCommerce Bookings

Lets your customers book services. Splash reads the booking dates and appends them to the
order line descriptions.

### Dokan Marketplace

Turns your shop into a multi-vendor marketplace.
[wordpress.org/plugins/dokan-lite](https://wordpress.org/plugins/dokan-lite/)

Once Dokan is active, the vendor id, code and name are added to every synchronized object, in
read-only.

### WP Multilang

A simple, efficient and free plugin to translate your contents, by Valentyn Riaboshtan.
[wordpress.org/plugins/wp-multilang](https://wordpress.org/plugins/wp-multilang/)

Its key feature is text serialization: it stores every translation in the original post, and
never creates duplicates.

### WPML

One of the most advanced translation plugins for WordPress.
[wpml.org](https://wpml.org/)

Splash reads your product translations in all the extra languages you configured.

### Wholesale Prices

Adds wholesale price levels to your products, by Wholesale Suite.
[wordpress.org/plugins/woocommerce-wholesale-prices](https://wordpress.org/plugins/woocommerce-wholesale-prices/)

Each price level is exposed by Splash as its own price field.
