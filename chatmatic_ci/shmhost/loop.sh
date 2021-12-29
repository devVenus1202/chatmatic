#!/bin/sh

trap exit TERM

# loop endlessly
while ${DEBUG_CMD:-true}
do
  sleep ${SLEEP_INTERVAL:-1}
done
