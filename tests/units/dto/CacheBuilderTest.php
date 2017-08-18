<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 18.08.17
 * Time: 19:59
 */

namespace microapi\dto;

use PHPUnit\Framework\TestCase;

class CacheBuilderTest extends TestCase {

    public function testBuild() {
        @unlink(TESTS_ROOT.'/units/dto/microapi_dto_A.php');
        @unlink(TESTS_ROOT.'/units/dto/microapi_dto_B.php');
        @unlink(TESTS_ROOT.'/units/dto/microapi_dto_C.php');
        self::assertFileNotExists(TESTS_ROOT.'/units/dto/microapi_dto_A.php');
        self::assertFileNotExists(TESTS_ROOT.'/units/dto/microapi_dto_B.php');
        self::assertFileNotExists(TESTS_ROOT.'/units/dto/microapi_dto_C.php');

        $b = new CacheBuilder(TESTS_ROOT.'/units/dto');
        $b->addNamespace('\microapi\dto', [TESTS_ROOT.'/units/dto']);
        $b->build();

        self::assertFileExists(TESTS_ROOT.'/units/dto/microapi_dto_A.php');
        self::assertFileExists(TESTS_ROOT.'/units/dto/microapi_dto_B.php');
        self::assertFileExists(TESTS_ROOT.'/units/dto/microapi_dto_C.php');
    }
}
