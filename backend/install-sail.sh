#!/bin/bash

# Info about this command:
# https://laravel.com/docs/8.x/sail#installing-composer-dependencies-for-existing-projects
# https://stackoverflow.com/questions/38437072/setup-laravel-project-after-cloning/65716541#65716541

docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v "$(pwd):/var/www/html" \
    -w /var/www/html \
    laravelsail/php81-composer:latest \
    composer install --ignore-platform-reqs
