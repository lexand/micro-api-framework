#!/usr/bin/env bash

if [ $# -ne 2 ]; then
    echo "$0: usage: start_test_server.sh <SERVER_ROOT> <PORT>"
    exit 1
fi

CURR_DIR=`pwd`
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" > /dev/null && pwd )"

PORT=$2

cd $1

if [ -f "test-server.pid" ]; then
  PID=`cat test-server.pid`
  kill ${PID}
  rm test-server.pid
fi

nohup php -S localhost:${PORT} ${SCRIPT_DIR}/router.php >> /dev/null &
echo $! > test-server.pid

cd ${CURR_DIR}
