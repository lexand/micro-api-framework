<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 12:12
 */

declare(strict_types=1);

namespace microapi\dto;

abstract class Validator {

    abstract public function validate(DTO $obj);

}