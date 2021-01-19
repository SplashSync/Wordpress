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

if [ ! -f /usr/local/bin/wp  ]; then
	echo "\n* Install Wordpress CLI ..."
	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli.phar
	mv wp-cli.phar /usr/local/bin/wp
fi

echo "\n* Install Php Soap Extension..."
apt-get update && apt-get install -y libxml2-dev default-mysql-client
docker-php-ext-install soap

cd $BUILD_DIR

echo "\n* Download Wordpress Core..."
wp core download --allow-root --version=$WORDPRESS_VERSION

echo "\n* Configure Wordpress Core..."

# wait until MySQL is really available
maxcounter=45
counter=1
while ! wp config create --allow-root --dbhost=mysql --dbname=wordpress --dbuser=root --dbpass=admin; do
    sleep 1
    echo "Waiting for MySQL..."
    counter=`expr $counter + 1`
    if [ $counter -gt $maxcounter ]; then
        >&2 echo "We have been waiting for MySQL too long already; failing."
        exit 1
    fi;
done

echo "\n* Install Wordpress Core..."
wp core install --allow-root --title="WP-SPLASH-CI" --admin_user=admin --admin_password=admin --admin_email=ci@splashsync.com