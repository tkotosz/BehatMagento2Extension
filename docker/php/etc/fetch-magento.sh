#!/usr/bin/env sh

if [ ! -f /var/www/html/composer.json ]; then
  rm -rf /var/www/html
  composer create-project --repository=https://repo.magento.com/ magento/project-community-edition=2.4.6 /var/www/html/
  cd /var/www/html
  composer require --dev behat/behat friends-of-behat/mink-extension behat/mink-goutte-driver tkotosz/test-area-magento2
fi
