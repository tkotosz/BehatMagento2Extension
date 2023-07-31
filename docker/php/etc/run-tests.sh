#!/usr/bin/env sh

php vendor/bin/phpunit tests/
php vendor/bin/behat --config /var/www/html/behat-magento2-extension/behat.yml --suite without_compiled_di --strict --stop-on-failure
php vendor/bin/behat --config /var/www/html/behat-magento2-extension/behat.yml --suite with_compiled_di --strict --stop-on-failure
