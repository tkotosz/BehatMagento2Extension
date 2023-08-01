Behat Magento2 Extension
======================

[![License](https://poser.pugx.org/nopenopenope/behat-magento2-extension/license)](https://packagist.org/packages/nopenopenope/behat-magento2-extension)
[![Latest Stable Version](https://poser.pugx.org/nopenopenope/behat-magento2-extension/version)](https://packagist.org/packages/nopenopenope/behat-magento2-extension)
![Build Status](https://github.com/nopenopenope/BehatMagento2Extension/actions/workflows/ci.yml/badge.svg)

This is a fork of the [BehatMagentoExtension](https://github.com/tkotosz/BehatMagento2Extension), which is
compatible with PHP8.1 and greater. This should ensure successful end-to-end testing of Magento 2 projects.

The `BehatMagento2Extension` provides a custom service container for Behat which allows to inject Magento services into
Behat Contexts and Behat helper services.

Installation
------------

The recommended installation method is through [Composer](https://getcomposer.org):

```bash
composer require seec/behat-magento2-extension
```

Usage
-----

In order to bootstrap Magento2 into your Behat suite, some modifications to the used behat.yml are required.

**Note**: If you use the Hooks provided by this package, your Magento Database will be purged and refilled with your
fixtures after each individual test.
This adds extra time to the execution but leaves your database also with DUMMY data. Do *not* use the hooks if you want
to keep your database intact. Do *not* use it on a production server if you don't know what you are doing.


Testing
-------

If you want to contribute to this module, make sure to run tests locally before committing. Docker Compose Containers
are set-up to run all tests for all PHP versions automatically, so testing is very easy.

```bash 
$ cp .env.dist .env // make sure to add your keys to the .env file otherwise testing will not work!
$ docker compose build
$ docker compose up -d
$ docker compose exec php sh
$ install-magento
$ install-extension
$ cd /var/www/html/vendor/seec/behat-magento2-extension
$ php vendor/bin/behat
```

Code Quality
------------

We aim for a unified code style; thus we enforce ECS and PHPStan onto our code. Make sure to run the following commands
before committing:

```bash
$ php vendor/bin/ecs check src/ tests/ features/ --fix
$ php vendor/bin/phpstan analyse src/ --level=8
$ php vendor/bin/phpstan analyse features/ --level=8
$ php vendor/bin/phpstan analyse tests/ --level=5
```

Documentation
-------------

The official documentation is available [here](https://behat-magento-2-extension.readthedocs.io/).
