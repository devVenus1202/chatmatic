#!/bin/bash


GIT_REPOS=(
        # git@github.com:travisstephenson/chatmatic_ci.git
	https://github.com/travisstephenson/chatmatic.git
	https://github.com/travisstephenson/chatmatic_pipeline.git
	https://github.com/travisstephenson/chatmatic_react.git
)

function do_pullall ()
{
    DIR=$(cd $(dirname "$0"); pwd -P)

    for name in "$DIR"/../*
    do
    if [ ! -h $name ] && [ -d "$name/.git" ]; then
        echo "#"
        echo "# Running git pull for $(basename $name)"
        cd "$name" && git pull
        echo
    fi
    done

    cd "$DIR"
}


function docker_compose_generic_cmd ()
{
    DIR=$(cd $(dirname "$0"); pwd -P)
    cd "$DIR" && COMPOSE_HTTP_TIMEOUT=200 /usr/local/bin/docker-compose "$@"
}


function do_build ()
{
    docker_compose_generic_cmd build "$@"
}


function do_up ()
{
    COMPOSE_HTTP_TIMEOUT=200 docker_compose_generic_cmd up -d "$@"
}


function do_down ()
{
    docker_compose_generic_cmd down -t 3 "$@"
}


function do_logs ()
{
    docker_compose_generic_cmd logs -f --tail 50 "$@"
}


function do_start ()
{
    docker_compose_generic_cmd start "$@"
}


function do_stop ()
{
    docker_compose_generic_cmd stop "$@"
}


function do_restart ()
{
    docker_compose_generic_cmd restart "$@"
}


function do_ps ()
{
    docker_compose_generic_cmd ps "$@"
}


function do_run ()
{
    docker_compose_generic_cmd run "$@"
}


function do_exec ()
{
    docker_compose_generic_cmd exec "$@"
}


function do_branches ()
{
    DIR=$(cd $(dirname "$0"); pwd -P)

    for name in "$DIR"/../*
    do
    if [ ! -h $name ] && [ -d "$name/.git" ]; then
        cmd="git status -sb"
        echo "#"
        echo "# $(basename $name)"
        cd "$name" && $cmd
        echo
    fi
    done

    cd "$DIR"
}

#
# "Attaches" local environment to remote, that is, forwards HTTP traffic from
# remote server to the local environment. This is achieved in 2 steps:
# 1. Starts SSH session to the remote server with reverse tunnel forwarding to
#    local environment.
# 2. Starts Goreplay forwarder on the remote server sending traffic over the
#    tunnel.
#
function do_attach ()
{

    # starts docker container with goreplay forwarder
    function goreplay_start ()
    {
        TAG=$1
        PORT=$2

        # extract private IP address assigned on eth0
        GOR_DST_ADDR=$(ip -br a show dev eth0 | grep -oE '10(\.[0-9]{1,3}){3}')

        # Check if there is a forwarder for this port already, which indicates
        # an old, dead session.
        # An SSH tunnel hes been started moments ago, traffic fowarder to this
        # port is just about to be started. If there is a forwarder sending
        # to the same port already, it must be a leftover from an old session.
        pattern="^goreplay_.*_${PORT}$"
        if docker ps --format="{{.Names}}" | grep -q "$pattern"
        then
            docker stop "$(grep "$pattern")"
        fi

        SESSION_NAME="goreplay_${TAG}_${PORT}"
        echo "Starting session \"$SESSION_NAME\""
        docker run \
            --rm \
            -it \
            --name $SESSION_NAME \
            --network container:chatmatic_ci_pipeline_1 \
            buger/goreplay -input-raw 0.0.0.0:9000 -output-http http://$GOR_DST_ADDR:$PORT -http-original-host
    }


    # finds dead sessions on the server and removes them
    function cleanup ()
    {
        for name in $(docker ps --format="{{.Names}}" | grep -E "^goreplay_.*[0-9]+$")
        do
            port=$(echo $name | grep -Eo "[0-9]+$")
            if netstat -nltp 2>/dev/null | grep -q 0.0.0.0:$port
            then
                # is this our session?
                if echo "$name" | grep -q "$TAG"
                then
                    echo "WARN: Active session found: \"$name\""
                fi
            else
                echo "WARN: Removing dead session: \"$name\""
                docker stop $name
            fi
        done
    }


    DIR=$(cd $(dirname "$0"); pwd -P)
    cd "$DIR" && . .env

    # random value between 20000 and 20099
    PORT=$((20000 + $(($RANDOM % 100)) ))
    # connection string like user@server, defaults to .env value
    ATTACH_CONN_STRING="${1:-$ATTACH_CONN_STRING}"
    # tag is used in container names, allows to match containers with people.
    # Defaults to local username.
    ATTACH_TAG=${ATTACH_TAG:-$(id -un)}

    # do it
    ssh \
        -t \
        -o ExitOnForwardFailure=yes \
        -R 0.0.0.0:$PORT:127.0.0.1:80 \
        "$ATTACH_CONN_STRING" \
        "$(typeset -f goreplay_start cleanup); cleanup $ATTACH_TAG; goreplay_start $ATTACH_TAG $PORT"
}


function do_init ()
{
    DIR=$(cd $(dirname "$0"); pwd -P)

    # clone repos
    cd "$DIR/.."
    for repo in "${GIT_REPOS[@]}"
    do
        echo "#"
        echo "# Cloning $repo"
        echo "#"
        git clone $repo
        echo
    done
    mkdir storage

    # set up each app requirements or workarounds

    # nginx / ssl, generate self signed certificate
    cd "$DIR/.."
    if [ ! -e "storage/letsencrypt/nginx_ssl_directives.inc" ]; then
        mkdir -m 777 storage/letsencrypt
        openssl req -x509 -sha256 -nodes -days 3650 -newkey rsa:2048 \
            -keyout "storage/letsencrypt/selfsigned.key" \
            -out    "storage/letsencrypt/selfsigned.pem" \
            -subj   "/CN=localhost"
        cat >"storage/letsencrypt/nginx_ssl_directives.inc" <<EOF
ssl_certificate     /etc/letsencrypt/selfsigned.pem;
ssl_certificate_key /etc/letsencrypt/selfsigned.key;
EOF
    fi

    # laravel app
    cd "$DIR/.."
    chmod -R go+w chatmatic/storage
    if [ ! -e "$DIR/../chatmatic/.env" ]; then
        echo "WARN: creating empty .env for laravel app, remember to populate it!"
    fi
    touch "$DIR/../chatmatic/.env"

    # chatmatic_web
    cd "$DIR/.."
    mkdir -m 777 storage/chatmatic-images

    # python daemons
    cd "$DIR/.."
    mkdir -m 777 storage/logs
    mkdir -m 777 storage/logs/pipeline
    mkdir -m 777 storage/logs/pipeline/queue
    mkdir -m 777 storage/opt-chatmatic

    # postgres
    cd "$DIR/.."
    mkdir storage/postgres

    # convenience symlinks to chatmatic_ci repo
    cd "$DIR/.."
    [ -e "ci" ] || ln -s chatmatic_ci ci

    # .env for docker-compose
    echo
    echo "# Populating environment general settings..."
    cd "$DIR"
    touch .env && . .env   # creates empty file if it doesn't exist, then loads

    if [ "x${PAPERTRAIL_SYSLOG_ADDRESS}" == "x" ]; then
        echo
        echo "# This controls Papertrail endpoint hostname / IP where logs will be sent to"
        read -p "PAPERTRAIL_SYSLOG_ADDRESS=" PAPERTRAIL_SYSLOG_ADDRESS
        echo "PAPERTRAIL_SYSLOG_ADDRESS=$PAPERTRAIL_SYSLOG_ADDRESS" >> .env
    fi
    if [ "x${PAPERTRAIL_SYSLOG_PORT}" == "x" ]; then
        echo
        echo "# This controls Papertrail endpoint port where logs will be sent"
        read -p "PAPERTRAIL_SYSLOG_PORT=" PAPERTRAIL_SYSLOG_PORT
        echo "PAPERTRAIL_SYSLOG_PORT=$PAPERTRAIL_SYSLOG_PORT" >> .env
    fi
    if [ "x${PAPERTRAIL_HOSTNAME}" == "x" ]; then
        echo
        echo "# This controls system name in Papertrail:"
        read -p "PAPERTRAIL_HOSTNAME=" PAPERTRAIL_HOSTNAME
        echo "PAPERTRAIL_HOSTNAME=$PAPERTRAIL_HOSTNAME" >> .env
    fi

    if [ "x${CHATMATIC_WEB_ENVIRONMENT_NAME}" == "x" ]; then
        CHATMATIC_WEB_ENVIRONMENT_NAME="chatmatic-test"
        # read -p "CHATMATIC_WEB_ENVIRONMENT_NAME=" CHATMATIC_WEB_ENVIRONMENT_NAME
        echo "CHATMATIC_WEB_ENVIRONMENT_NAME=$CHATMATIC_WEB_ENVIRONMENT_NAME" >> .env
    fi

    if [ "x$STORAGE" == "x" ]; then
        STORAGE="../storage"
        # read -p "STORAGE=" STORAGE
        echo "STORAGE=$STORAGE" >> .env
    fi

    if [ "x$HOST_USER_ID" == "x" ]; then
        echo "HOST_USER_ID=$(id -u)" >> .env
    fi

    if [ "x$ATTACH_CONN_STRING" == "x" ]; then
        echo
        echo "# This controls SSH connection string for ./attach action."
        echo "# Format is username@serveraddr or just serveraddr,"
        echo "# examples: cm@chatdeploy.com, chatdeploy.com, user@1.2.3.4."
        read -p "ATTACH_CONN_STRING=" ATTACH_CONN_STRING
        echo "ATTACH_CONN_STRING=$ATTACH_CONN_STRING" >> .env
    fi

    if [ "x$ATTACH_TAG" == "x" ]; then
        echo
        echo "./attach action creates named sessions on the server and ATTACH_TAG"
        echo "is part of that name. This allows to match sessions with person"
        echo "that creates them, just set it to a word that identifies you."
        echo "Leave it empty and it will default to your local user account"
        echo "name, in this case: \"$(id -un)\"".
        echo "Try to avoid collision with another developer."
        read -p "ATTACH_TAG=" ATTACH_TAG
        echo "ATTACH_TAG=${ATTACH_TAG:-$(id -un)}" >> .env
    fi

    cat <<-"EOF"

Initialization complete. Post installation steps:
1. Install Docker: https://docs.docker.com/install/.
2. Install Docker Compose (only for Linux): https://docs.docker.com/compose/install/.
3. Add yourself to "docker" group (only for Linux): `sudo usermod -aG docker $(id -un)` and log out / log in.
4. Checkout appropriate branch for each repo.
5. Build docker images with `./build` (takes a few minutes to complete).
6. Populate .env file in Laravel repo.
7. Run composer install for Laravel app: `./run chatmatic_laravel composer install`.
8. Add this line to your hosts file: '127.0.0.1  admin api'
9. Start the environemnt locally with `./up` and visit http://admin/.
EOF
}


case "$(basename $0)" in
    "pullall")
        do_pullall
        ;;

    "build")
        docker_compose_generic_cmd build "$@"
        ;;

    "up")
        do_up "$@"
        ;;

    "down")
        do_down "$@"
        ;;

    "logs")
        do_logs "$@"
        ;;

    "start")
        do_start "$@"
        ;;

    "stop")
        do_stop "$@"
        ;;

    "restart")
        do_restart "$@"
        ;;

    "ps")
        do_ps "$@"
        ;;

    "run")
        do_run "$@"
        ;;

    "exec")
        do_exec "$@"
        ;;

    "pbul")
        do_pullall \
        && do_build \
        && do_up \
        && do_logs
        ;;

    "branches")
        do_branches
        ;;

    "init")
        do_init
        ;;

    "attach")
        do_attach "$@"
        ;;

    *)
        echo "Unknown command: $(basename $0)."
        exit 1
        ;;

esac
