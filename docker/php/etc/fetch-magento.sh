#!/usr/bin/env sh

PHP_VERSION=$(php -v | tac | tail -n 1 | cut -d " " -f 2 | cut -c 1-3)
echo "$PHP_VERSION"

if [ ! -f /var/www/html/composer.json ]; then
  rm -rf /var/www/html
  if [ "$PHP_VERSION" = "7.4" ]; then
    composer create-project --no-install --repository=https://repo.magento.com/ magento/project-community-edition=2.4.3-p3 /var/www/html/
  else
    composer create-project --no-install --repository=https://repo.magento.com/ magento/project-community-edition=2.4.6 /var/www/html/
  fi
  cd /var/www/html
  composer config --no-plugins allow-plugins.magento/* true
  composer config --no-plugins allow-plugins.php-http/discovery true
  composer config --no-plugins allow-plugins.laminas/laminas-dependency-plugin true
  composer config --no-plugins allow-plugins.dealerdirect/phpcodesniffer-composer-installer true
  composer require --dev behat/behat friends-of-behat/mink-extension behat/mink-goutte-driver tkotosz/test-area-magento2
  composer install
fi
