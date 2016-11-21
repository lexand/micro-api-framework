#!/usr/bin/env bash

if [ $# -ne 1 ]; then
    echo "$0: usage: stop_test_server.sh <SERVER_ROOT>"
    exit 1
fi

CURR_DIR=`pwd`

cd $1

if [ -f "test-server.pid" ]; then
  PID=`cat test-server.pid`
  kill ${PID}
  rm test-server.pid
fi

cd ${CURR_DIR}

