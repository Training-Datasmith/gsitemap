#!/usr/bin/env bash
set -euo pipefail
cd /app
if [ ! -f vendor/bin/phpunit ]; then
  php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
  php -r "copy('https://composer.github.io/installer.sig', 'installer.sig');"
  ACTUAL=$(php -r "echo hash_file('sha384', 'composer-setup.php');")
  EXPECTED=$(php -r "echo trim(file_get_contents('installer.sig'));")
  if [ "$ACTUAL" != "$EXPECTED" ]; then
    echo "Composer installer checksum mismatch" >&2
    exit 1
  fi
  php composer-setup.php --version=2.2.25 --filename=composer.phar
  php composer.phar install --no-interaction
fi
./vendor/bin/phpunit -c tests/phpunit.xml.dist
bash tests/bin/run-shuffled.sh
