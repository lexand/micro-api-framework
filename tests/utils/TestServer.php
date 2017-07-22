<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 14.04.16
 * Time: 11:38
 */

namespace tests\utils;

class TestServer {
    const PORT = '9898';

    public static function start() {
        $cmd = TESTS_ROOT . '/functional/start_test_server.sh ' . TESTS_ROOT . '/data' . ' ' . TestServer::PORT;
        shell_exec($cmd);
        TestServer::wait();
    }

    public static function stop() {
        shell_exec(TESTS_ROOT . '/functional/stop_test_server.sh ' . TESTS_ROOT . '/data');
    }

    public static function wait() {
        // wait for true
        $ch = curl_init('http://localhost:' . static::PORT.'/ping');
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        Wait::explicit(
            function () use ($ch) {
                $res      = curl_exec($ch);
                $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                if (($httpCode === 200) && ($res === 'pong')) {
                    return true;
                }

                return false;
            },
            10
        );
        curl_close($ch);
    }
}