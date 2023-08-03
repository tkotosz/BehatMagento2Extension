#!/usr/bin/env sh

cd /var/www/html
echo "Installing Behat Magento 2 Extension in Developer Magento Installation"
composer config repositories.behat-m2-extension path vendor/seec/behat-magento2-extension
composer require seec/behat-magento2-extension:@dev
cd /var/www/html/vendor/seec/behat-magento2-extension
echo "Installing Extension Composer Dependencies"
composer install
cd /var/www/html
composer dump-autoload -o
