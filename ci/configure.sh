#!/bin/sh
################################################################################
#
#  This file is part of SplashSync Project.
#
#  Copyright (C) Splash Sync <www.splashsync.com>
#
#  This program is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
#
#  For the full copyright and license information, please view the LICENSE
#  file that was distributed with this source code.
#
#  @author Bernard Paquier <contact@splashsync.com>
#
################################################################################

cd "$BUILD_DIR"  || exit

echo "* Enable WooCommerce Cost of Goods Sold Feature..."
wp option update woocommerce_feature_cost_of_goods_sold_enabled yes --allow-root

echo "* Configure WooCommerce Default Tax Rate (20%)..."
wp eval '
    if (class_exists("WC_Tax") && empty(WC_Tax::get_rates_for_tax_class(""))) {
        WC_Tax::_insert_tax_rate(array(
            "tax_rate_country"  => "",
            "tax_rate_state"    => "",
            "tax_rate"          => "20.0000",
            "tax_rate_name"     => "TVAFR20",
            "tax_rate_priority" => 1,
            "tax_rate_compound" => 0,
            "tax_rate_shipping" => 1,
            "tax_rate_order"    => 0,
            "tax_rate_class"    => "",
        ));
    }
' --allow-root

echo "* Enable Splash Orders Addresses Synchronization..."
wp option update splash_sync_order_shipping on --allow-root
wp option update splash_sync_order_billing on --allow-root

echo "* Enable & Configure Splash Plugin..."
wp plugin activate splash-connector --allow-root
wp option update splash_ws_id       ThisIsWpKey                     --allow-root
wp option update splash_ws_key      ThisTokenIsNotSoSecretChangeIt  --allow-root
wp option update splash_ws_protocol SOAP                            --allow-root
wp option update splash_ws_user     1                               --allow-root
