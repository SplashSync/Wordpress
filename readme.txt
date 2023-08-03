=== Splash Sync ===
Contributors: BadPixxel
Donate link: http://www.splashsync.com
Tags: wordpress, woocommerce, splash, synchronization, e-commerce, ERP, prestashop, magento, dolibarr
Requires at least: 6.1
Tested up to: 6.2
Stable tag: 2.0.6
License: MIT
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Splash, the synchronization system of innovative companies!

This module implement Splash Sync connector for WordPress & WooCommerce. It provides access to multiples Objects for automated synchronization through Splash Sync dedicated protocol.

Access to WooCommerce objects is also managed if the plugin is found and active.

== Description ==

= Splash, the synchronization system of innovative companies! =

Splash is an innovative synchronization system for a multitude of reasons! Thanks to its declarative strategy, Splash is an open system capable of handling any type of data, whatever the complexity. Universal, it does not worry about the type of data: an invoice, a customer, a blog article, a comment, all are only objects composed of fields that will have to be synchronized.

= Fully Universal =

Change the way you manage your apps in the cloud! Splash is a data connector unlike any other. Why? It is totally universal!!

= Synchronize all types of data =

Our goal is very simple, connect and synchronize your data between all the applications you use, whatever they are.

= Simplify your e-Commerce management =

Synchronize your stocks between several merchant sites? Share your customer data between all your services? With Splash, it's not just possible, it's easy and without developments.

= More about Splash =

This module is part of SplashSync project.

For more information about Splash Sync, the way it works and how you can use it to connect your applications, please refer our online documentation.

= Key features & benefits =

This module will give Splash access to ThirdParty, Products, Customer Orders & Invoice.

= Synchronize Products Stocks =

Centralize your products stocks from Dolibarr to any kind of applications.

= Merge all your customers data =

Once all your modules connected, use the Object Linked to identify and merge all your customers profiles into a single Splash entity. This way, all similar information will be shared and synchronized anywhere, from CRM to E-Commerce.

= Consolidate & Simplify your Financial Analytics =

If WooCommerce is you main site, orders and invoices can be automatically imported from your others E-Commerce, point-of-sale, or any other applications you may connect!

Your financial analytics is easier... and with no efforts.

= Already Compatible Applications =

This plugin will provide Splash Connector for WordPress base and WooCommerce Plugin.

You can use it to synchronize WordPress and WooCommerce with any of other Splash compatible application: Dolibarr, PrestaShop, Magento, Sylius, MailChimp, MailJet.

== Installation ==

Installing "SPlash Sync Plugin" can be done either by searching for "WordPress Plugin Template" via the "Plugins > Add New" screen in your WordPress dashboard, or by using the following steps:

1. Download the plugin via WordPress.org or splashsync.com
1. Upload the ZIP file through the 'Plugins > Add New > Upload' screen in your WordPress dashboard
1. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==

1. Overview of Products synchronized by Splash on multiple websites

== Frequently Asked Questions ==

= Is Splash for Free ? =

Create your account, connect two servers and test Splash for free. Then, if it matchs your needs, just update your account, it starts from 8€/month.

== Changelog ==

= 0.9 =
* 2017-05-12
* Initial release

= 1.0.0 =
* 2017-07-04
* Release of First Stable Version V1.0.0

= 1.0.1 =
* 2017-08-22
* BugFix : Removed warning on module's assets loading.

= 1.0.2 =
* 2017-08-24
* BugFix : Improve: Product Stock Management now use WooCommerce Business Logic

= 1.1.0 =
* 2017-09-28
* Improve:  Added Product Variation access for WooCommerce
* Improve:  Added Notifications on Admin Site
* BugFix:   Update of Product Prices
* BugFix:   User Listing now includes all roles
* BugFix:   Mapping of Invoice & Orders Fields now compliant with Dolibarr standards

= 1.1.1 =
* 2017-10-12
* BugFix:   Cleaned Warning for "settings_assets" 
* BugFix:   Cleaned Notice for Static Notifications Call

= 1.2.0 =
* 2017-10-17
* Improve:      Now Compatible with WordPress Multisite features. Each site will be considered as a separate server.
* New Feature:  Added Access to WooCommerce Customer Address
* New Feature:  Added Links to Billing & Shipping Address in Order Objects 

= 1.2.1 =
* 2017-11-03
* Improve:      Added Status Field on Customer Invoices to Simplify Export on Dolibarr

= 1.2.2 =
* 2017-11-12
* Improve:      Added Hooks on Product Stocks Updates 

= 1.3.0 =
* 2018-02-01
* Improve:      Now access Users without WooCommerce installed
* Improve:      Upgrade of Splash Pḧp-Core Module (Now Implements HTTPS)

= 1.3.1 =
* 2018-03-13
* BugFix:       Orders & Invoices may return wrong Prices if Empty Amount

= 1.5.1 =
* 2019-10-23
* BugFix:       Creation of Variable Products

= 1.5.2 =
* 2020-01-17
* BugFix:       Search for Existing Images
* BugFix:       Limit number of custom fields to 200
* Improve:      Passed PhpStan V0.12 (Level 8) 

= 1.6.0 =
* 2020-07-07
* Fix:          Sync of large files on WordPres 5.3+
* Fix:          Products listing total counters
* Improve:      Add protections against Anonymized Orders (Filter Commits, Disable reading)
* Improve:      Add management for Variations Stocks at parent level
* Improve:      Add Configuration for Objects Custom fields

= 1.6.1 =
* 2020-08-22
* Improve:      Add Products Categories Sync

= 1.6.3 =
* 2021-03-08
* Added:        Compatibility with Dokan plugin

= 1.7.0 =
* 2021-03-23
* Beta:        Compatibility with Wpml plugin

= 1.7.1 =
* 2021-07-06
* Beta:        Compatibility with Wholesale Prices for WooCommerce by Wholesale Suite
* 2021-08-26
* Added:       Added Variant Parents SKU

= 1.7.2 =
* 2021-09-15
* Added:       Compatibility with Wc Pdf Invoices plugin

= 1.7.3 =
* 2022-02-09
* Added:       Wordpress 5.9 Compatibility
* Added:       Wc Orders Addresses as Text

= 2.0.0 =
* 2022-05-09
* Refactor:    Migrate to Php Core V2
* Added:       WordPress 6.0 && 6.1 Compatibility
* Added:       PHO 8.0 && 8.1 Compatibility

= 2.0.6 =
* 2023-08-03
* Refactor:    Order Class Imports
* Added:       Colissimo Statuses Compatibility
* Added:       Orders & Invoices Status Encoding in Lists

== Upgrade Notice ==

= 1.0.0 =
* 2017-07-04
* Release of first stable version.
