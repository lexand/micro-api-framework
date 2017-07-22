<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 16:01
 */

namespace microapi;

use app\controller\Test6547586Ctl;
use PHPUnit\Framework\TestCase;

class DispatcherTest extends TestCase {

    public function testGetEndpointFromCache() {

        $d = new Dispatcher();
        $d->addDefaultModule('\app');
        $d->setEndpointCachePath(TESTS_ROOT. '/data');

        $end = $d->getEndpointFromCache('get', Test6547586Ctl::class, 'get');

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

    public function testGetEndpointFromReflection() {
        $d = new Dispatcher();
        $d->addDefaultModule('\app');

        $end = $d->getEndpointFromReflection('get', Test6547586Ctl::class, 'get');

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

    public function testGetEndpoint() {
        $_SERVER['REQUEST_URI'] = '/test6547586/get';
        $_SERVER['REQUEST_METHOD'] = 'get';

        $d = new Dispatcher();
        $d->addDefaultModule('\app');
        $d->preDispatch();

        $end = $d->getEndpoint();

        self::assertNotNull($end);

        $res = $end->invoke();
        static::assertEquals((new Test6547586Ctl())->actionGet(), $res);
    }

}
