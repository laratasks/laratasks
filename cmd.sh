#!/usr/bin/env bash

# ================== #
# ===  COMMANDS  === #
# ================== #
case "$1" in
    "attach-to-shell")
        service="$2"
        user_id="$3"

        [[ -z ${service} ]] && service="php-fpm"
        [[ -z ${user_id} ]] && user_id=$UID

        docker-compose exec -u ${user_id} ${service} sh
    ;;
esac
