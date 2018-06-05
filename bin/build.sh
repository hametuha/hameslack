#!/usr/bin/env bash

set -e

composer install --no-dev
#npm install
#npm start
#rm -rf node_modules

rm -rf .travis.yml
rm .gitignore
rm phpunit.xml.dist
rm -rf tests
rm -rf bin

if [ $TRAVIS_TAG ]; then
    echo $TRAVIS_TAG
fi
if [ $SVN_USER ]; then
    echo "SVN_USER exists."
fi
if [ $SVN_PASS ]; then
    echo "SVN_PASS exists."
fi
# Make Readme
echo 'Generate readme.'
curl -L https://raw.githubusercontent.com/fumikito/wp-readme/master/wp-readme.php | php
