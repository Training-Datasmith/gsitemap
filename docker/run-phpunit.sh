#!/usr/bin/env bash
set -euo pipefail
cd /app

if [ -z "${GSITEMAP_DB_PORT:-}" ]; then
  echo "GSITEMAP_DB_PORT must be set (use docker/run-tests.sh on the host to start pinned MariaDB on 3307)" >&2
  exit 1
fi

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

exec "$@"
