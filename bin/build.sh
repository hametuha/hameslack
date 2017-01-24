#!/usr/bin/env bash

set -e

#composer install --no-dev
#npm install
#npm start
#rm -rf node_modules

# Make Readme
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
