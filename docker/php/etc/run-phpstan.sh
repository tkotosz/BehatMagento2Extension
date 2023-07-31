#!/usr/bin/env sh

php vendor/bin/phpstan analyse src/ --level=8
php vendor/bin/phpstan analyse features/ --level=6
php vendor/bin/phpstan analyse tests/ --level=6
