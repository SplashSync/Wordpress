
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
#!/bin/sh

if [ ! -f /usr/local/bin/wp  ]; then

	echo "\n* Install Wordpress CLI ..."

	curl -O https://raw.githubusercontent.com/wp-cli/builds/gh-pages/phar/wp-cli.phar
	chmod +x wp-cli.phar
	mv wp-cli.phar /usr/local/bin/wp

fi

if [ ! -f wp-config.php ]; then

	echo "\n* Download Wordpress Core..."

	wp core download --allow-root --version=$WORDPRESS_VERSION 

	echo "\n* Configure Wordpress Core..."

	# wait until MySQL is really available
	maxcounter=45
	counter=1
	while ! wp config create --allow-root --dbhost=$WORDPRESS_DB_HOST --dbname=$WORDPRESS_DB_NAME --dbuser=$WORDPRESS_DB_USER --dbpass=$WORDPRESS_DB_PASSWORD --dbprefix=$WORDPRESS_TABLE_PREFIX --skip-check > /dev/null 2>&1; do
	    sleep 1
	    echo "Waiting for MySQL..."
	    counter=`expr $counter + 1`
	    if [ $counter -gt $maxcounter ]; then
	        >&2 echo "We have been waiting for MySQL too long already; failing."
	        exit 1
	    fi;
	done

	echo "\n* Install Wordpress Core..."
	wp core install --allow-root --url=$WORDPRESS_URL --title="WP-SPLASH" --admin_user=admin --admin_password=$ADMIN_PASSWD --admin_email=$ADMIN_MAIL 


	echo "\n* Install WooCommerce Plugin ..."

	wp plugin install woocommerce --allow-root --activate
	wp option update woocommerce_currency EUR --allow-root

	echo "\n* Install Wordpress Additionnal Plugins ..."
	wp plugin install wp-multilang --allow-root --activate

	echo "\n* Install Wordpress for Splash ..."
	wp plugin activate splash-connector --allow-root
	wp option update splash_ws_id $SPLASH_WS_ID --allow-root
	wp option update splash_ws_key $SPLASH_WS_KEY --allow-root
	wp option update splash_ws_protocol SOAP --allow-root
	wp option update splash_advanced_mode "on" --allow-root
	wp option update splash_server_url $SPLASH_WS_HOST --allow-root
	wp option update splash_ws_user 1 --allow-root

fi

echo "\n* Install Php Soap Extension..."

apt-get update && apt-get install -y libxml2-dev
docker-php-ext-install soap

echo "\n* Almost ! Starting web server now\n";
exec apache2-foreground