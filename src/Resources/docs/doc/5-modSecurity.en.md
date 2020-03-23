---
lang: en
permalink: docs/modSecurity
title: Improve security
---

If you want to secure your WordPress site by protecting direct access to the wp-content folder, it's possible !!

Two possibilities to do so:
* Activate the Apache ModSecurity module. By default it will filter requests in the wp-content / plugins folder.
* Use an .htaccess file to manually filter requests.

To be able to continue using Splash, simply add a redirect file to the root of your site.

* Download it here: [github.com/SplashSync/Wordpress/blob/master/src/Resources/support/splash-endpoint.php](https://raw.githubusercontent.com/SplashSync/Wordpress/master/src/Resources/support/splash-endpoint.php)
* Add the file to the root of your site /www/my-website/splash-endpoint.php
* On your Splash account, change the address of your site (Webservice path).
* Replace /my-website/wp-content/plugins/splash-connector/vendor/splash/phpcore/soap.php
* By /my-website/splash-endpoint.php

<div class="callout-block callout-warning">
    <div class="icon-holder">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="content">
        <h4 class="callout-title">Please note, more and more web hosts are integrating that kind of security by default.</h4>
        <p>
            If you are in this case, you will have no other choice than to carry out this manipulation.
        </p>
    </div>
</div>