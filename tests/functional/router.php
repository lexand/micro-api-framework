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

$d = \microapi\Dispatcher::get()->addDefaultModule('\app');

$d->on(
    'afterdispatch',
    [
        function (\microapi\event\Event $e): \microapi\event\Event {
            /** @var \microapi\event\AfterDispatch $e */
            if ($e->data instanceof Throwable) {
                $e->setStopped();
                header('Content-Type: application/json');
                echo json_encode(
                    [
                        'error' => true,
                        'msg'   => $e->data->getMessage()
                    ]
                );
            }

            return $e;
        },
        function (\microapi\event\Event $e): \microapi\event\Event {
            /** @var \microapi\event\AfterDispatch $e */
            header('Content-Type: application/json');
            echo json_encode($e->data);

            return $e;
        }
    ]
);

$d->dispatch();
