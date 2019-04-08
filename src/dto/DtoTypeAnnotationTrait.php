<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 15.08.17
 * Time: 22:58
 */
declare(strict_types=1);

namespace microapi\dto;

use microapi\util\Type;

trait DtoTypeAnnotationTrait {

    public static function annotatedMeta(string $docs): array {
        $matched = [];

        $type    = null;
        $isArray = false;
        if (\preg_match('/@var\s+([\w\\\]+(?:\[\])?)/', $docs, $matched)) {
            $type = $matched[1];
            $isArray = Type::isArray($type, $type);
        }

        $builtin = ($type !== null) ? Type::isBuiltIn($type) : false;
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
            'exposed' => \preg_match('/@exposed/', $docs) >= 1
        ];

        return $res;
    }

}