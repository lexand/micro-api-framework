<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 14:48
 */

namespace microapi\endpoint;

use PHPUnit\Framework\TestCase;

class CacheBuilderTest extends TestCase {


    public function testExtractData() {
        $cb = new CacheBuilder();

        $cb->setCachePath(__DIR__)
           ->addModulesNamespace('app', [TESTS_ROOT . '/functional/']);

        $res = $cb->extractData();

        static::assertEquals(require TESTS_ROOT . '/data/raw_cache.php', $res);
    }

    public function testBuild() {
        $cb = new CacheBuilder();

        $cb->setCachePath(__DIR__)
           ->addModulesNamespace('app', [TESTS_ROOT . '/functional/']);

        $cb->build();

        self::assertTrue(file_exists(__DIR__ . '/get.php'));
        self::assertTrue(file_exists(__DIR__ . '/post.php'));


        self::assertEquals(require TESTS_ROOT . '/data/endpoints_get.php', require 'get.php');
        self::assertEquals(require TESTS_ROOT . '/data/endpoints_post.php', require 'post.php');
    }
}