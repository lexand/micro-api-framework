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

        $cb->setCachePath(TESTS_ROOT . '/units/endpoint')
           ->addModulesNamespace('app', [TESTS_ROOT . '/classes']);

        $res = $cb->extractData();

        static::assertEquals(require TESTS_ROOT . '/data/raw_cache.php', $res);
    }

    public function testBuild() {
        $path = TESTS_ROOT . '/units/endpoint';

        $cb = new CacheBuilder();

        $cb->setCachePath($path)
           ->addModulesNamespace('app', [TESTS_ROOT . '/classes']);

        $cb->build();

        self::assertTrue(file_exists($path . '/endpoints_get.php'));
        self::assertTrue(file_exists($path . '/endpoints_post.php'));


        self::assertEquals(require TESTS_ROOT . '/data/endpoints_get.php', require $path . '/endpoints_get.php');
        self::assertEquals(require TESTS_ROOT . '/data/endpoints_post.php', require $path . '/endpoints_post.php');
    }

    public function testBuildWithModules() {
        $path = TESTS_ROOT . '/units/endpoint';

        $cb = new CacheBuilder();

        $cb->setCachePath($path)
           ->addModulesNamespace('app', [TESTS_ROOT . '/classes'])
           ->addModulesNamespace('admin', [TESTS_ROOT . '/classes']);

        $cb->build();

        self::assertTrue(file_exists($path . '/endpoints_get.php'));
        self::assertTrue(file_exists($path . '/endpoints_post.php'));


        self::assertEquals(require TESTS_ROOT . '/data/mods_endpoints_get.php', require $path . '/endpoints_get.php');
        self::assertEquals(require TESTS_ROOT . '/data/mods_endpoints_post.php', require $path . '/endpoints_post.php');
    }

}