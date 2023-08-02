#!/usr/bin/env sh

rm -f app/etc/env.php
mkdir -p pub/static pub/media
$(which php) bin/magento setup:install \
      --admin-email "magento@magento.com" \
      --admin-firstname "admin" \
      --admin-lastname "admin" \
      --admin-password "admin123!#" \
      --admin-user "admin" \
      --backend-frontname admin \
      --base-url "http://magento.test" \
      --db-host mysql \
      --db-name magento \
      --db-user root \
      --db-password magento \
      --session-save files \
      --use-rewrites 1 \
      --use-secure 0 \
      --search-engine="opensearch" \
      --opensearch-host="opensearch" \
      --opensearch-port="9200" \
      --timezone="Europe/Amsterdam" \
      --skip-db-validation \
      --cleanup-database \
      -vvv
$(which php) bin/magento deploy:mode:set developer
composer dump-autoload
$(which php) bin/magento setup:upgrade
composer config minimum-stability dev
