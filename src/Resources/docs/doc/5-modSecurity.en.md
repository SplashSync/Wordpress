---
lang: en
permalink: docs/modSecurity
title: Improve security
description: Keep Splash working when direct access to the wp-content folder is filtered.
updated: 2026-09-24
---

You can protect your WordPress site by blocking direct access to the `wp-content` folder,
either by enabling the Apache **ModSecurity** module — which filters requests to
`wp-content/plugins` by default — or by filtering them yourself from an `.htaccess` file.

Splash reaches your site through a file that lives in that very folder, so such a filter cuts
the connection. Adding a small redirect file at the root of your site restores it.

### Set up the redirect

1. Download [splash-endpoint.php](https://raw.githubusercontent.com/SplashSync/Wordpress/2.0/src/Resources/support/splash-endpoint.php)
   from the [plugin repository](https://github.com/SplashSync/Wordpress/blob/2.0/src/Resources/support/splash-endpoint.php).
2. Drop it at the root of your site, as `/www/my-website/splash-endpoint.php`.
3. On your Splash account, edit your server and change the **Webservice path**, from
   `/my-website/wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php`
   to `/my-website/splash-endpoint.php`.

> [!WARNING]
> More and more hosting providers turn this kind of filtering on by default. If yours does,
> this redirect is not an option: it is the only way to keep Splash connected.
