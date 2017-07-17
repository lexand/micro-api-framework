<?php

declare(strict_types=1);

require_once __DIR__ . '/../config/web_app.php';

/** @var \microapi\base\WebUser $webUser */
$webUser = \microapi\Dispatcher::get()->getComp('user');

if ($webUser->isLoggedIn()) {
    include APP_ROOT . '/views/app.php';
}
else {
    include APP_ROOT . '/views/login.php';
}

// or you can use
// $app->dispatch();
// to handle GET requests or POST requests without DTO objects
