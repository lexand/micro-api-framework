<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 15.08.17
 * Time: 23:08
 */
declare(strict_types=1);

namespace microapi\dto;

use PHPUnit\Framework\TestCase;

class DtoTypeAnnotationTraitTest extends TestCase {
    /**
     * @dataProvider annotatedMetaBuiltinDP
     */
    public function testAnnotatedMetaBuiltin(string $type, bool $isArray, bool $exposed) {

        $arr  = $isArray ? '[]' : '';
        $exp  = $exposed ? '@exposed' : '';
        $docs = <<< __DOCS__
/**
 * @var {$type}{$arr} 
 * {$exp}
 */
__DOCS__;

        $meta = DtoTypeAnnotationTrait::annotatedMeta($docs);

        $ref = [
            'isDto'   => false,
            'isArray' => $isArray,
            'type'    => $type,
            'builtin' => true,
            'exposed' => $exposed
        ];

        asort($ref);
        asort($meta);

        self::assertEquals($ref, $meta);
    }

    public function annotatedMetaBuiltinDP() {
        return [
            // exposed scalar 0-4
            ['string', false, true],
            ['int', false, true],
            ['float', false, true],
            ['double', false, true],
            ['bool', false, true],
            // exposed array of scalars 5-9
            ['string', true, true],
            ['int', true, true],
            ['float', true, true],
            ['double', true, true],
            ['bool', true, true],
            // not exposed scalar 10-14
            ['string', false, false],
            ['int', false, false],
            ['float', false, false],
            ['double', false, false],
            ['bool', false, false],
            // not exposed array of scalars 15-19
            ['string', true, false],
            ['int', true, false],
            ['float', true, false],
            ['double', true, false],
            ['bool', true, false],

        ];
    }

    /**
     * @dataProvider annotatedMetaWrongClassDP
     */
    public function testAnnotatedMetaWrongClass(string $type, bool $isArray, bool $exposed) {

        $arr  = $isArray ? '[]' : '';
        $exp  = $exposed ? '@exposed' : '';
        $docs = <<< __DOCS__
/**
 * @var {$type}{$arr} 
 * {$exp}
 */
__DOCS__;

        $meta = DtoTypeAnnotationTrait::annotatedMeta($docs);

        $ref = [
            'isDto'   => false,
            'isArray' => $isArray,
            'type'    => null,
            'builtin' => false,
            'exposed' => $exposed
        ];

        asort($ref);
        asort($meta);

        self::assertEquals($ref, $meta);
    }

    public function annotatedMetaWrongClassDP() {
        return [
            // no types at all 0-3
            ['dummy', false, false],
            ['dummy', true, false],
            ['dummy', false, true],
            ['dummy', true, true],
            // absent classes
            ['\some\not\existsing\class', false, false],
        ];
    }

    /**
     * @dataProvider annotatedMetaForDtoDP
     */
    public function testAnnotatedMetaForDto(string $class, bool $isArray, bool $exposed) {
        $arr = $isArray ? '[]' : '';
        $exp = $exposed ? '@exposed' : '';

        $docs = <<< __DOCS__
/**
 * @var {$class}{$arr} 
 * {$exp}
 */
__DOCS__;

        $meta = DtoTypeAnnotationTrait::annotatedMeta($docs);

        $ref = [
            'isDto'   => true,
            'isArray' => $isArray,
            'type'    => $class,
            'builtin' => false,
            'exposed' => $exposed
        ];

        asort($ref);
        asort($meta);

        self::assertEquals($ref, $meta);
    }

    public function annotatedMetaForDtoDP() {
        return [
            [B::class, false, false],
            [B::class, true, false],
            [B::class, false, true],
            [B::class, true, true],
            [C::class, true, true]
        ];
    }

    /**
     * @dataProvider annotatedMetaForObjDP
     */
    public function testAnnotatedMetaForObj(string $class, bool $isArray, bool $exposed) {
        $arr = $isArray ? '[]' : '';
        $exp = $exposed ? '@exposed' : '';

        $docs = <<< __DOCS__
/**
 * @var {$class}{$arr} 
 * {$exp}
 */
__DOCS__;

        $meta = DtoTypeAnnotationTrait::annotatedMeta($docs);

        $ref = [
            'isDto'   => false,
            'isArray' => $isArray,
            'type'    => $class,
            'builtin' => false,
            'exposed' => $exposed
        ];

        asort($ref);
        asort($meta);

        self::assertEquals($ref, $meta);
    }

    public function annotatedMetaForObjDP() {
        return [
            [Point::class, false, false],
            [Point::class, true, false],
            [Point::class, false, true],
            [Point::class, true, true]
        ];
    }

}