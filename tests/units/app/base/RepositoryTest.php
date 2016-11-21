<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 16:04
 */

namespace microapi\base;

class RepositoryTest extends \PHPUnit_Framework_TestCase {

    public function testAddAndGet() {
        $r = new Repository();

        $r->add('test', 0);
        static::assertEquals(0, $r->get('test'));
    }

    public function testAddAndLazyGet() {
        $r = new Repository();

        $r->add('test', function () { return 1000; });
        static::assertEquals(1000, $r->get('test'));
    }

}