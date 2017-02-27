#!/bin/bash
# simple script to run unit and scenario tests for the app

# due to phpunit's config containing relative pathes phpunit should be run from
# inside the src directory
cd ${0%/*}/..

# verify syntax
echo "I: Verifying syntax ..."
find -type f -name *\.php -exec php -l {} \; || exit 1
echo

# verify class checks
echo "I: Verifying class checks ..."
bins/verifyclasschecks.sh || exit 2
echo

# run phpunit
mkdir -p ../../doc/phpunit
echo "I: Running unit tests ..."
# !!! overriding the exception handler before running unit tests might not be
#     a good idea
phpunit --verbose --no-globals-backup -c conf/phpunit/phpunit.xml \
        --testsuite heili || exit 3
echo

cd - >/dev/null
