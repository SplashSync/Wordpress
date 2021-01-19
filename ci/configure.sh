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

echo "* Enable & Configure Splash Plugin..."
wp plugin activate splash-connector --allow-root
wp option update splash_ws_id       ThisIsWpKey                     --allow-root
wp option update splash_ws_key      ThisTokenIsNotSoSecretChangeIt  --allow-root
wp option update splash_ws_protocol SOAP                            --allow-root
wp option update splash_ws_user     1                               --allow-root
