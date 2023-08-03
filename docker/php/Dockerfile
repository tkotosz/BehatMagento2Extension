ARG PHP_VERSION=php:cli-alpine3.18
ARG XDEBUG_VERSION=xdebug
FROM $PHP_VERSION
ARG XDEBUG_VERSION
ARG MAGENTO_PUBLIC_KEY=1
ARG MAGENTO_SECRET_KEY=1

ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DOCKER_BUILDKIT=1

WORKDIR /var/www/

RUN apk add --update --no-cache linux-headers
RUN set -xe \
    && apk update \
    && apk add -u \
        git \
        vim \
        nano \
        gpg-agent \
        openssh-client \
        nano \
        wget \
        gnupg \
        unzip \
        autoconf \
        gcc \
        gettext \
        libxml2-dev \
        zip \
        gd

RUN apk add --no-cache mysql-client msmtp perl procps shadow libzip libpng libjpeg-turbo libwebp freetype icu icu-data-full libxslt-dev libgcrypt-dev libxml2-dev pcre-dev ${PHPIZE_DEPS}
RUN apk add --no-cache --virtual build-essentials \
    icu-dev icu-libs zlib-dev g++ make automake autoconf libzip-dev \
    libpng-dev libwebp-dev libjpeg-turbo-dev freetype-dev && \
    docker-php-ext-configure gd --enable-gd --with-freetype --with-jpeg --with-webp && \
    docker-php-ext-install gd && \
    docker-php-ext-install mysqli && \
    docker-php-ext-install pdo_mysql && \
    docker-php-ext-install intl && \
    docker-php-ext-install opcache && \
    docker-php-ext-install exif && \
    docker-php-ext-install xml && \
    docker-php-ext-install zip && \
    docker-php-ext-install bcmath && \
    docker-php-ext-install soap && \
    docker-php-ext-install sockets && \
    docker-php-ext-install xsl && \
    pecl install $XDEBUG_VERSION && \
    docker-php-ext-enable xdebug && \
    apk del build-essentials pcre-dev ${PHPIZE_DEPS} && rm -rf /usr/src/php*

COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

COPY ./docker/php/etc/auth.json.tmpl        /tmp/auth.json.tmpl
COPY ./docker/php/etc/999-php-custom.ini    /usr/local/etc/php/conf.d/999-php-custom.ini
COPY ./docker/php/etc/entrypoint.sh         /usr/local/bin/entrypoint
COPY ./docker/php/etc/test-magento.sh       /usr/local/bin/test-magento
COPY ./docker/php/etc/run-ecs.sh            /usr/local/bin/run-ecs
COPY ./docker/php/etc/run-phpstan.sh        /usr/local/bin/run-phpstan
COPY ./docker/php/etc/run-tests.sh          /usr/local/bin/run-tests
COPY ./docker/php/etc/fetch-magento.sh      /usr/local/bin/fetch-magento
COPY ./docker/php/etc/install-magento.sh    /usr/local/bin/install-magento
COPY ./docker/php/etc/install-extension.sh  /usr/local/bin/install-extension
COPY ./docker/php/etc/create-auth-file.sh   /usr/local/bin/create-auth-file

RUN mkdir -p ~/.composer
RUN chmod +x /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/test-magento
RUN chmod +x /usr/local/bin/run-ecs
RUN chmod +x /usr/local/bin/run-phpstan
RUN chmod +x /usr/local/bin/run-tests
RUN chmod +x /usr/local/bin/install-magento
RUN chmod +x /usr/local/bin/fetch-magento
RUN chmod +x /usr/local/bin/install-extension
RUN chmod +x /usr/local/bin/create-auth-file

RUN create-auth-file
RUN rm -rf /var/www/html/*
RUN fetch-magento

COPY ./src /var/www/html/vendor/seec/behat-magento2-extension/src
COPY ./features /var/www/html/vendor/seec/behat-magento2-extension/features
COPY ./tests /var/www/html/vendor/seec/behat-magento2-extension/tests
COPY ./behat.yml.dist /var/www/html/vendor/seec/behat-magento2-extension/behat.yml.dist
COPY ./ecs.php /var/www/html/vendor/seec/behat-magento2-extension/ecs.php
COPY ./phpstan.neon /var/www/html/vendor/seec/behat-magento2-extension/phpstan.neon
COPY ./composer.json /var/www/html/vendor/seec/behat-magento2-extension/composer.json
COPY ./composer.lock /var/www/html/vendor/seec/behat-magento2-extension/composer.lock
COPY ./vendor /var/www/html/vendor/seec/behat-magento2-extension/vendor
RUN install-extension

WORKDIR /var/www/html/vendor/seec/behat-magento2-extension

ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["tail", "-F", "/var/www/html/php_error.log"]
