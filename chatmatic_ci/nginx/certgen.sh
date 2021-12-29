#!/bin/sh

BASE_DIR=${1:-"$( cd $(dirname $0); pwd )"}
KEY_FILE_NAME="selfsigned.key"
CERT_FILE_NAME="selfsigned.pem"

apk add --no-cache openssl
openssl req -x509 -sha256 -nodes -days 3650 -newkey rsa:2048 -keyout "$BASE_DIR/$KEY_FILE_NAME" -out "$BASE_DIR/$CERT_FILE_NAME" -subj "/CN=localhost"
cat >"$BASE_DIR/nginx_ssl_directives.inc" <<EOF
ssl_certificate     /etc/letsencrypt/$CERT_FILE_NAME;
ssl_certificate_key /etc/letsencrypt/$KEY_FILE_NAME;
EOF
