#!/bin/sh

set -e

## Variables
#USER=www-data
#CURRENT_UID=$(id -u "$USER")
#CURRENT_GID=$(id -g "$USER")
#
## Set UID / Shell for user
#if [ "$UID" != "$CURRENT_UID" ]; then
#  usermod -u "${UID}" -s /bin/sh "$USER"
#fi
#
## Set GID for user
#if [ "$UID" != "$CURRENT_GID" ]; then
#  groupmod -g "${GID}" "$USER"
#fi

if [ "$1" = 'php-fpm' ] || [ "$1" = 'php' ] || [ "$1" = 'bin/console' ]; then

    # Clear cache
    php bin/console cache:clear --no-warmup > /dev/null

    # Warmup cache
    php bin/console cache:warmup > /dev/null


	mkdir -p var/cache var/log var/cache/prod

  setfacl -R -m u:www-data:rwX -m u:"$(whoami)":rwX var
  setfacl -dR -m u:www-data:rwX -m u:"$(whoami)":rwX var

exec docker-php-entrypoint "$@"
else
  exec "$@"
fi

