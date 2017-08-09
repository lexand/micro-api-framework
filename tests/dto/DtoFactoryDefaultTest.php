<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 09.08.17
 * Time: 21:06
 */

namespace microapi\dto;

use PHPUnit\Framework\TestCase;

class DtoFactoryDefaultTest extends TestCase {

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

        $meta = DtoFactoryDefault::annotatedMeta($docs);

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

        $meta = DtoFactoryDefault::annotatedMeta($docs);

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

        $meta = DtoFactoryDefault::annotatedMeta($docs);

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

        $meta = DtoFactoryDefault::annotatedMeta($docs);

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

    /**
     * @expectedException \microapi\dto\DtoFieldExposingException
     */
    public function testExposedException() {

        $df = new DtoFactoryDefault();

        $df->createFromAssoc(\microapi\dto\A::class, ['exposedWithoutType' => 123]);
    }

    public function testFillScalar() {

        $df = new DtoFactoryDefault();

        $fields = [
            's' => 'string',
            'i' => 123,
            'f' => 0.123,
            'd' => 123.456,
            'b' => true
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        foreach ($fields as $name => $value) {
            self::assertEquals($value, $obj->{$name}, "field {$name} not filled properly");
        }
    }

    public function testFillArrayOfScalar() {

        $df = new DtoFactoryDefault();

        $fields = [
            'sArr' => ['string'],
            'iArr' => [123],
            'fArr' => [0.123],
            'dArr' => [123.456],
            'bArr' => [true]
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        foreach ($fields as $name => $value) {
            self::assertCount(1, $obj->{$name});
            self::assertEquals($value, $obj->{$name}, "field {$name} not filled properly");
        }
    }

    /**
     * @expectedException \microapi\dto\DtoFieldTypeMismatched
     */
    public function testFillArrayOfScalarFromNotScalarSrc() {

        $df = new DtoFactoryDefault();

        $fields = [
            'sArr' => 'string',
            //'i' => 123,
            //'f' => 0.123,
            //'d' => 123.456,
            //'b' => true
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        foreach ($fields as $name => $value) {
            self::assertEquals($value, $obj->{$name}, "field {$name} not filled properly");
        }
    }

    public function testFillNestedDto() {
        $df = new DtoFactoryDefault();

        $fields = [
            'dto' => ['a' => 'string'],
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        /** @var \microapi\dto\A $obj */
        self::assertInstanceOf(B::class, $obj->dto);
        self::assertEquals($obj->dto->a, $fields['dto']['a']);
    }

    public function testFillNestedArrayOfDto() {
        $df = new DtoFactoryDefault();

        $fields = [
            'dtoArr' => [['a' => 'string']],
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        /** @var \microapi\dto\A $obj */
        self::assertCount(1, $obj->dtoArr);
        self::assertInstanceOf(B::class, $obj->dtoArr[0]);
        self::assertEquals($obj->dtoArr[0]->a, $fields['dtoArr'][0]['a']);
    }

    public function testFillNestedObj() {
        $df = new DtoFactoryDefault();

        $fields = [
            'point' => ['x' => 1, 'y' => 2],
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        /** @var \microapi\dto\A $obj */
        self::assertInstanceOf(Point::class, $obj->point);
        self::assertEquals($obj->point->x, $fields['point']['x']);
        self::assertEquals($obj->point->y, $fields['point']['y']);
    }

    public function testFillNestedArrayOfObj() {
        $df = new DtoFactoryDefault();

        $fields = [
            'points' => [['x' => 1, 'y' => 2]],
        ];
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertInstanceOf(A::class, $obj);
        /** @var \microapi\dto\A $obj */
        self::assertInstanceOf(Point::class, $obj->points[0]);
        self::assertCount(1, $obj->points);
        self::assertEquals($obj->points[0]->x, $fields['points'][0]['x']);
        self::assertEquals($obj->points[0]->y, $fields['points'][0]['y']);
    }

    public function testNotExposedDontTouched() {
        $df = new DtoFactoryDefault();

        $fields = [
            'notExposed' => A::VALUE + 10
        ];

        /** @var \microapi\dto\A $obj */
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertEquals(A::VALUE, $obj->notExposed);
    }

    public function testExposedButNotListedInDataDontTouched() {
        $df = new DtoFactoryDefault();

        $fields = [
            's' => 'string'
        ];

        /** @var \microapi\dto\A $obj */
        $obj    = $df->createFromAssoc(\microapi\dto\A::class, $fields);

        self::assertEquals(A::VALUE, $obj->exposedWithDefault);
    }
}

class A extends DTO {
    const VALUE = 154;

    /**
     * @var int
     */
    public $notExposed = self::VALUE;

    /**
     * @var int
     * @exposed
     */
    public $exposedWithDefault = self::VALUE;

    /**
     * @var string
     * @exposed
     */
    public $s;

    /**
     * @var int
     * @exposed
     */
    public $i;

    /**
     * @var float
     * @exposed
     */
    public $f;

    /**
     * @var double
     * @exposed
     */
    public $d;

    /**
     * @var bool
     * @exposed
     */
    public $b;

    /**
     * @exposed
     */
    public $exposedWithoutType;

    /**
     * @var int[]
     * @exposed
     */
    public $iArr;

    /**
     * @var bool[]
     * @exposed
     */
    public $bArr;

    /**
     * @var string[]
     * @exposed
     */
    public $sArr;

    /**
     * @var float[]
     * @exposed
     */
    public $fArr;

    /**
     * @var double[]
     * @exposed
     */
    public $dArr;

    /**
     * @var \microapi\dto\Point
     * @exposed
     */
    public $point;

    /**
     * @var \microapi\dto\Point[]
     * @exposed
     */
    public $points;

    /**
     * @var \microapi\dto\B
     * @exposed
     */
    public $dto;

    /**
     * @var \microapi\dto\B[]
     * @exposed
     */
    public $dtoArr;
}

class B extends DTO {
    /**
     * @var string
     * @exposed
     */
    public $a;
}

class C extends B {
}

class Point {
    public $x;
    public $y;

    public function __construct(array $data) {
        $this->x = $data['x'];
        $this->y = $data['y'];
    }
}
