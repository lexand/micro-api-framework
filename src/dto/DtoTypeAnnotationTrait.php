<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 15.08.17
 * Time: 22:58
 */
declare(strict_types=1);

namespace microapi\dto;

trait DtoTypeAnnotationTrait {

    private static $buildInTypes = [
        'int'     => 1,
        'integer' => 1,
        'string'  => 1,
        'float'   => 1,
        'double'  => 1,
        'bool'    => 1,
        'boolean' => 1
    ];

    public static function annotatedMeta(string $docs): array {
        $matched = [];

        $type    = null;
        $isArray = false;
        if (preg_match('/@var\s+([\w\\\]+(?:\[\])?)/', $docs, $matched)) {
            $type = $matched[1];
            if (strrpos($type, '[]', -2)) {
                $type    = substr($type, 0, -2);
                $isArray = true;
            }
        }

        $builtin = ($type !== null) ? isset(self::$buildInTypes[$type]) : false;
        $isDto   = false;
        if (!$builtin) {
            try {
                $r = new \ReflectionClass($type);
                if ($r->isSubclassOf(DTO::class)) {
                    $isDto = true;
                }
            }
            catch (\Throwable $t) {
                $type    = null;
                $builtin = false;
            }
        }

        $res = [
            'type'    => $type,
            'isDto'   => $isDto,
            'isArray' => $isArray,
            'builtin' => $builtin,
            'exposed' => preg_match('/@exposed/', $docs) >= 1
        ];

        return $res;
    }

}