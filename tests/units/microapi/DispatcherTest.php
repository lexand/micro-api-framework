<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 16:01
 */

namespace microapi;

use app\controller\Test6547586Ctl;
use GuzzleHttp\Psr7\ServerRequest;
use microapi\event\Event;
use microapi\util\Tokenizer;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase {

    public function testGetEndpointFromCache() {

        $sr = new ServerRequest('get', '/');

        $d = new Dispatcher();
        $d->addDefaultModule('\app');
        $d->setEndpointCachePath(TESTS_ROOT . '/data');

        $end = $d->getEndpointFromCache($sr, Test6547586Ctl::class, 'get');

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

    public function testGetEndpointFromReflection() {
        $sr = new ServerRequest('get', '/');

        $d = new Dispatcher();
        $d->addDefaultModule('\app');

        $end = $d->getEndpointFromReflection($sr, Test6547586Ctl::class, 'get');

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

    public function testGetEndpoint() {
        $sr = new ServerRequest(
            'get',
            '/test6547586/get'
        );

        $d = new Dispatcher();
        $d->addDefaultModule('\app');


        $end = $d->getEndpoint(
            new Tokenizer($sr->getUri()->getPath(), '/', 0),
            $sr
        );

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

    public function testDispatch() {
        $_SERVER['REQUEST_URI']    = '/test6547586/get';
        $_SERVER['REQUEST_METHOD'] = 'get';

        $data = [];

        $d = new Dispatcher();
        $d->addDefaultModule('\app');
        $d->on(
            'afterdispatch',
            [
                function (Event $e) use (&$data) {
                    /** @var \microapi\event\object\AfterDispatch $e */
                    $data = $e->data;

                    return $e;
                }
            ]
        );
        $d->dispatch();

        static::assertEquals((new Test6547586Ctl())->actionGet(), $data);
    }
}
