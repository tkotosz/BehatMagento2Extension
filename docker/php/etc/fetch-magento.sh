#!/usr/bin/env sh

if [ ! -f /var/www/html/composer.json ]; then
  rm -rf /var/www/html
  composer create-project --no-install --repository=https://repo.magento.com/ magento/project-community-edition=2.4.3-p3 /var/www/html/
  cd /var/www/html
  composer config --no-plugins allow-plugins.magento/* true
  composer config --no-plugins allow-plugins.php-http/discovery true
  composer config --no-plugins allow-plugins.laminas/laminas-dependency-plugin true
  composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
  composer require --dev behat/behat friends-of-behat/mink-extension behat/mink-goutte-driver
  composer install
fi
