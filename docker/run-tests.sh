#!/usr/bin/env bash
set -euo pipefail
ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

bash docker/mariadb.sh

export GSITEMAP_DB_HOST=127.0.0.1
export GSITEMAP_DB_NAME=gsitemap
export GSITEMAP_DB_USER=gsitemap
export GSITEMAP_DB_PASSWORD=gsitemap
export GSITEMAP_DB_PORT=3307

PHP_IMAGE="${GSITEMAP_PHP_IMAGE:-gsitemap-php56}"

run_in_php() {
  docker run --rm --network host \
    -v "$ROOT":/app -w /app \
    -e GSITEMAP_DB_HOST -e GSITEMAP_DB_PORT -e GSITEMAP_DB_NAME -e GSITEMAP_DB_USER -e GSITEMAP_DB_PASSWORD \
    "$PHP_IMAGE" bash docker/run-phpunit.sh "$@"
}

run_in_php ./vendor/bin/phpunit -c tests/phpunit.xml.dist
run_in_php ./vendor/bin/phpunit -c tests/phpunit.xml.dist
run_in_php bash -lc 'GSITEMAP_SHUFFLE_SEED=20261008 bash tests/bin/run-shuffled.sh'
