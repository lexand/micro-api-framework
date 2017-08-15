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
     * @expectedException \microapi\dto\DtoFieldExposingException
     */
    public function testExposedException() {

        $df = new DtoFactoryDefault();

        $df->createFromData(\microapi\dto\A::class, ['exposedWithoutType' => 123]);
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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

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
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

        self::assertEquals(A::VALUE, $obj->notExposed);
    }

    public function testExposedButNotListedInDataDontTouched() {
        $df = new DtoFactoryDefault();

        $fields = [
            's' => 'string'
        ];

        /** @var \microapi\dto\A $obj */
        $obj    = $df->createFromData(\microapi\dto\A::class, $fields);

        self::assertEquals(A::VALUE, $obj->exposedWithDefault);
    }
}
