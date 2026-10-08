#!/usr/bin/env bash
set -euo pipefail
cd /app
SCRIPT_DIR="$(cd "$(dirname "$0")" && pwd)"

if command -v docker >/dev/null 2>&1; then
  bash "$SCRIPT_DIR/mariadb.sh"
  export GSITEMAP_DB_HOST=127.0.0.1
  export GSITEMAP_DB_NAME=gsitemap
  export GSITEMAP_DB_USER=gsitemap
  export GSITEMAP_DB_PASSWORD=gsitemap
  # Host port published by docker/mariadb.sh
  export GSITEMAP_DB_PORT=3307
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

./vendor/bin/phpunit -c tests/phpunit.xml.dist
GSITEMAP_SHUFFLE_SEED=20261008 bash tests/bin/run-shuffled.sh
