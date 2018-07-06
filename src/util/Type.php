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

    private static $buildInTypes = [
        'int'     => 1,
        'integer' => 1,
        'string'  => 1,
        'float'   => 1,
        'double'  => 1,
        'bool'    => 1,
        'boolean' => 1
    ];

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

    public static function isBuiltIn(string $type): bool { return isset(self::$buildInTypes[$type]); }

    public static function isArray(string $type, string &$elementType) : bool {
        $res = (\substr($type, -2) === '[]');
        if ($res) {
            $elementType = \substr($type, 0, -2);

            return true;
        }

        $elementType = $type;

        return false;
    }
}