### ——————————————————————————————————————————————————————————————————
### —— Local Makefile
### ——————————————————————————————————————————————————————————————————

include vendor/badpixxel/php-sdk/make/sdk.mk

WORKDIR := "/var/www/html/wp-content/plugins/splash-connector"
WP_CMD := $(DOCKER_COMPOSE) exec -w $(WORKDIR) wordpress-6.7

test: ## Execute Functional Test
	@$(MAKE) up
	@$(WP_CMD) wp plugin delete wp-multilang --allow-root
	@$(WP_CMD) php vendor/bin/phpunit -c grumphp/phpunit.xml.dist --testsuite=Local
	@$(WP_CMD) php vendor/bin/phpunit -c grumphp/phpunit.xml.dist
