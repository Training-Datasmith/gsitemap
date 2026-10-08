#!/usr/bin/env bash
set -euo pipefail

# linux/amd64 manifest digest for mariadb:10.6 (plan pin)
MARIADB_IMAGE="mariadb@sha256:23616f0bd3aff922f4dea4130f1d0a09f3571d20b7b36c8f49840672dc309e8c"
CONTAINER_NAME="${GSITEMAP_MARIADB_CONTAINER:-gsitemap-mariadb-test}"

if docker inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
  existing_image="$(docker inspect --format '{{.Config.Image}}' "$CONTAINER_NAME")"
  if [ "$existing_image" != "$MARIADB_IMAGE" ]; then
    echo "Removing MariaDB container $CONTAINER_NAME (image $existing_image != $MARIADB_IMAGE)" >&2
    docker rm -f "$CONTAINER_NAME"
  fi
fi

if ! docker inspect "$CONTAINER_NAME" >/dev/null 2>&1; then
  docker run -d --name "$CONTAINER_NAME" \
    -e MARIADB_ROOT_PASSWORD=root \
    -e MARIADB_DATABASE=gsitemap \
    -e MARIADB_USER=gsitemap \
    -e MARIADB_PASSWORD=gsitemap \
    -p 127.0.0.1:3307:3306 \
    "$MARIADB_IMAGE"
else
  docker start "$CONTAINER_NAME" >/dev/null 2>&1 || true
fi

for _ in $(seq 1 60); do
  if docker exec "$CONTAINER_NAME" mariadb-admin ping -h127.0.0.1 -uroot -proot --silent >/dev/null 2>&1; then
    docker exec "$CONTAINER_NAME" mariadb -uroot -proot -N -e 'SELECT VERSION();'
    exit 0
  fi
  sleep 1
done

echo "MariaDB container did not become ready" >&2
exit 1
