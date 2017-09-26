<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 26.09.17
 * Time: 19:37
 */
declare(strict_types=1);

namespace microapi\util;

class Type {

    public static function cast(string $buildInType, $value) {
        switch ($buildInType) {
            case 'string':
                return (string)$value;
            case 'int':
            case 'integer':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'double':
                return (double)$value;
            case 'bool':
            case 'boolean':
                return (bool)$value;
            default:
                throw new \LogicException('incorrect type');
        }
    }

}