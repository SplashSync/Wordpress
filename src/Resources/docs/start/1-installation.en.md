---
lang: en
permalink: start/install
title: Install the Plugin
description: Requirements, installation from the WordPress dashboard or by hand.
updated: 2026-09-24
---

### Requirements

Before you start, check that your site meets the following:

* PHP **7.4** or above
* WordPress **6.3** or above, tested up to WordPress **7.0**
* WooCommerce **8.0** or above, *optional*, to synchronize your shop
* An active Splash Sync account

### Install from your dashboard

WordPress installs plugins on its own, without ever touching a file. From your admin, go to
**Plugins > Add New**, search for **Splash Connector**, then **Install Now** and **Activate**.

![Splash Connector in the WordPress plugin directory](../assets/img/wordpress-plugin-install.png "Plugins > Add New")

This is the recommended way. If your host blocks it, install the plugin by hand instead.

### Install by hand

* Download the latest stable release from [splashsync.com](https://www.splashsync.com/en/modules/)
* Unzip it into your plugins folder, as `wp-content/plugins/splash-connector`
* Activate **Splash Connector** from the **Plugins** screen

> [!NOTE]
> Keep the `splash-connector` folder name: it is part of the webservice address that Splash
> uses to reach your site.

Once the plugin is active, move on to the configuration.
