<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 15.08.17
 * Time: 23:13
 */
declare(strict_types=1);

namespace microapi\dto;

class Point {
    public $x;
    public $y;

    public function __construct(array $data) {
        $this->x = $data['x'];
        $this->y = $data['y'];
    }
}
