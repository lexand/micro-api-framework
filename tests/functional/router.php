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
                    ->addDefaultModule('\app')
                    ->on(
                        'afterdispatch',
                        [
                            function (\microapi\event\Event $e): \microapi\event\Event {
                                /** @var \microapi\event\object\AfterDispatch $e */
                                if ($e->wr instanceof Throwable) {
                                    $e->setStopped();
                                    header('Content-Type: application/json');
                                    echo json_encode(
                                        [
                                            'error' => true,
                                            'msg'   => $e->wr->getMessage()
                                        ]
                                    );
                                }

                                return $e;
                            },
                            function (\microapi\event\Event $e): \microapi\event\Event {
                                /** @var \microapi\event\object\AfterDispatch $e */
                                header('Content-Type: application/json');
                                echo json_encode($e->wr->data);

                                return $e;
                            }
                        ]
                    )
                    ->dispatch();
