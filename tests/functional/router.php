<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 30.09.16
 * Time: 13:42
 */

require_once __DIR__ . '/../../vendor/autoload.php';

$uri = $_SERVER['REQUEST_URI'];

if ($uri === '/ping') {
    echo 'pong';
    exit(0);
}

// router.php
if (preg_match('/\.(?:png|jpg|jpeg|gif)$/', $_SERVER["REQUEST_URI"])) {
    return false;
}

\microapi\Dispatcher::get()
                    ->addDefaultModule('\app\controller')
                    ->dispatch();
