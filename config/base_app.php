<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 31.10.16
 * Time: 11:46
 */

require_once(__DIR__ . '/main-config.php');

$cfg = require(__DIR__ . '/config.php');

$app = \microapi\Dispatcher::get();

$app->addComp('log', function () use ($cfg){return new \microapi\log\Logger($cfg['log']);});
