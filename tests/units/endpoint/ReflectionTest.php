<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 11:47
 */

namespace microapi\endpoint;

use PHPUnit\Framework\TestCase;

class ReflectionTest extends TestCase {

    /**
     * @methods(get, post, dummy)
     */
    protected function methodWithDocBlock() {

    }

    /**
     * @methods (get, post, dummy)
     */
    protected function methodWithDocBlockAnotherRepresentaion() {

    }


    public function testGetActionHttpMethods() {
        $mr = new \ReflectionMethod(ReflectionTest::class, 'methodWithDocBlock');
        $res = Reflection::getActionHttpMethods($mr);
        static::assertEquals(['get','post','dummy'], $res);
    }

    public function testGetActionHttpMethodsAnotherRepresentation() {
        $mr = new \ReflectionMethod(ReflectionTest::class, 'methodWithDocBlockAnotherRepresentaion');
        $res = Reflection::getActionHttpMethods($mr);
        static::assertEquals(['get','post','dummy'], $res);
    }

}