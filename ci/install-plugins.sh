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

echo "\n* Install WooCommerce Plugin ..."

wp plugin install woocommerce --allow-root --activate
wp option update woocommerce_currency EUR --allow-root

echo "\n* Install Wordpress Additional Plugins ..."
wp plugin install wp-multilang --allow-root --activate

echo "\n* Install Wordpress for Splash ..."