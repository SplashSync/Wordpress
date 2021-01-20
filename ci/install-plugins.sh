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

echo "* Install WooCommerce Plugin..."

wp plugin install woocommerce --allow-root --activate
wp option update woocommerce_currency EUR --allow-root

echo "* Install Wordpress Additional Plugins..."
wp plugin install wp-multilang --allow-root

echo "* Install Splash Plugin for Wordpress..."
mv "/builds/SplashSync/Wordpress/" "$PLUGIN_DIR"
cd "$PLUGIN_DIR"  || exit
curl -s https://raw.githubusercontent.com/BadPixxel/Php-Sdk/main/ci/composer.sh | bash

cd "$BUILD_DIR"  || exit