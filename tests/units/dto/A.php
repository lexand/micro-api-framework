<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 15.08.17
 * Time: 23:12
 */
declare(strict_types=1);

namespace microapi\dto;

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
