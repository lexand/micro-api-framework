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


require_once __DIR__ . '/../classes/app/controller/Test6547586Ctl.php';
require_once __DIR__ . '/../classes/admin/controller/Test6547586Ctl.php';
require_once __DIR__ . '/../classes/dto/TestDTO.php';

// here is example of the microapi-framework usage

\microapi\Dispatcher::get()
                    ->addModule('admin', '\admin')
                    ->addDefaultModule('\app')
                    ->setResponseFactory(
                        new \microapi\http\DefaultResponseFactory(
                            200,
                            ['Content-Type' => 'application/json']
                        )
                    )
                    ->on(
                        'afterdispatch',
                        [
                            function (\microapi\event\Event $e): \microapi\event\Event {
                                /** @var \microapi\event\object\AfterDispatch $e */
                                if ($e->wr->data instanceof Throwable) {
                                    $code   = 500;
                                    $reason = '';
                                    if ($e->wr->data instanceof HttpException) {
                                        $code   = $e->wr->data->getCode();
                                        $reason = $e->wr->data->getMessage();
                                    }
                                    $e->wr->response = $e->wr->response->withStatus($code, $reason);
                                    // or you can render pretty html for Content-Type: text/html and for debug mode
                                    $e->wr->data = [
                                        'error' => true,
                                        'msg'   => $e->wr->data->getMessage()
                                    ];
                                }

                                return $e;
                            },
                            function (\microapi\event\Event $e): \microapi\event\Event {
                                /** @var \microapi\event\object\AfterDispatch $e */
                                if ($e->wr->data !== null) {
                                    $r = $e->wr->response;

                                    switch (strtolower($r->getHeaderLine('Content-Type'))) {
                                        case 'text/plain':
                                            $body = \GuzzleHttp\Psr7\stream_for(print_r($e->wr->data));
                                            break;
                                        case 'text/html':
                                            $body = \GuzzleHttp\Psr7\stream_for('Some HTML here');
                                            // init renderer
                                            // get tpl
                                            // render tpl with data
                                            // use partial render for xhr request
                                            // use full render for non-xhr request
                                            break;
                                        case 'application/json':
                                        default:
                                            $body = \GuzzleHttp\Psr7\stream_for(json_encode($e->wr->data));
                                            break;
                                    }

                                    $e->wr->response = $r->withBody($body);
                                    $e->wr->data     = null;
                                }

                                return $e;
                            },
                            function (\microapi\event\Event $e): \microapi\event\Event {
                                /** @var \microapi\event\object\AfterDispatch $e */
                                if ($e->wr->data === null) {
                                    \microapi\send_response($e->wr->response);
                                }

                                return $e;
                            }
                        ]
                    )
                    ->dispatch();
