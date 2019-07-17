#!/usr/bin/env bash

TAG=$(git describe --tags | sed 's/v//g')
echo "Build release ${TAG}"
cd Libraries/

COMPOSER=$(which composer);
if [[ -z "${COMPOSER}" ]];
then
    if [[ -e /usr/local/bin/composer.phar ]]; then
        /usr/local/bin/composer.phar install -q
    fi
else
    composer install -q
fi

cd ..
zip -r -qq -9 ../mautic_$TAG.zip * -x "*.git" -x ".*" -x "Libraries/composer.*" -x "Build/"
rm -rf Libraries/vendor/
echo "Done"