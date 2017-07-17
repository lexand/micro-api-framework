<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 31.10.16
 * Time: 12:10
 */

require_once(__DIR__ . '/base_app.php');

$app = \microapi\Dispatcher::get();

$app->addComp('user', function () use ($cfg){return new \microapi\base\WebUser();});
