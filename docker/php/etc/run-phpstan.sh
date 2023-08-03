#!/usr/bin/env sh

cd /var/www/html/vendor/seec/behat-magento2-extension
php vendor/bin/phpstan analyse src/ --level=8
php vendor/bin/phpstan analyse features/ --level=6
php vendor/bin/phpstan analyse tests/ --level=6
