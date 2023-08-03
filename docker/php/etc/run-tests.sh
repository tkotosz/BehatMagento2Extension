#!/usr/bin/env sh

cd /var/www/html/vendor/seec/behat-magento2-extension
php vendor/bin/phpunit tests/
php vendor/bin/behat --stop-on-failure
