<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 12:35
 */

return [
    'log' => [
        'enabled'      => true,
        'level'        => \Psr\Log\LogLevel::DEBUG,
        'name'         => 'APP',
        'handlerClass' => '\Monolog\Handler\StreamHandler',
        'handler.path' => APP_ROOT . '/logs/app.log'
    ]
];
