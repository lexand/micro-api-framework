<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 15:10
 */

namespace app\dto;

use microapi\dto\DTO;

class TestDTO extends DTO{

    /**
     * @var string
     * @exposed
     */
    public $name;

    /**
     * @var int
     * @exposed
     */
    public $age;
}
