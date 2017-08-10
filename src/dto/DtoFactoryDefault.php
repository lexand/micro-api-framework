<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:39
 */

declare(strict_types=1);

namespace microapi\dto;

use Psr\Http\Message\StreamInterface;

/**
 * Class DtoFactoryDefault
 *
 * DTO objects should accept next conventions
 *
 * - such fields should annotated with its type (builtin or class) and @exposed. Fields with @exposed and without type
 * will throw exception DtoFieldExposingException. Fields without @exposed will be meant as auxiliary.
 * - DTO class supports nesting of DTO and general objects. Nested DTO objects will instantiated with the same
 * DtoFactory. General objects will be created as "new $class($rawData)"
 * - DTO supports fields with arrayed type (array od scalars/objects)
 *
 *
 * @package microapi\dto
 * @see \microapi\dto\DTO
 */
class DtoFactoryDefault implements DtoFactory {

    private static $buildInTypes = [
        'int'     => 1,
        'integer' => 1,
        'string'  => 1,
        'float'   => 1,
        'double'  => 1,
        'bool'    => 1,
        'boolean' => 1,
    ];

    public function createFromStream(string $class, StreamInterface $stream): DTO {
        $fields = json_decode($stream->getContents(), true);

        return $this->createFromData($class, $fields);
    }

    public function createFromData(string $class, array $fields): DTO {

        $obj = new $class();

        $meta = $this->metaFor($class);
        if ($meta === []) {
            $this->fillObjViaReflection($obj, $fields);
        }
        else {
            $this->fillObjViaMeta($obj, $fields, $meta);
        }

        return $obj;
    }


    /**
     * @param \microapi\dto\DTO $obj
     * @param array             $fields
     * @throws \microapi\dto\DtoFieldExposingException
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    public function fillObjViaReflection(DTO $obj, array $fields) {
        $r     = new \ReflectionObject($obj);
        $props = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $name = $prop->getName();
            if (isset($fields[$name])) {
                $meta = self::annotatedMeta($prop->getDocComment());

                if (!$meta['exposed']) {
                    continue;
                }

                $this->fillFeild($obj, $fields, $meta, $name);
            }
        }
    }

    public static function annotatedMeta(string $docs) {
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

    public function fillObjViaMeta(DTO $obj, array $fields, array $fieldsMeta) {
        foreach ($fieldsMeta as $name => $meta) {
            if (isset($fields[$name])) {
                $this->fillFeild($obj, $fields, $meta, $name);
            }
        }
    }

    public function metaFor(string $class) {
        // todo: read from cache
        return [];
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * As we accept RAW JSON POST we assume that all fields already exists with correct type.
     *
     * If you have another abnormal behavior in you application you must override this method.
     *
     * Without casting you still can fill DTO fields with unpredictable values so be careful.
     *
     * @param \microapi\dto\DTO $obj     DTO object
     * @param string            $field   field name in object
     * @param string            $type    build in type (see \microapi\dto\DtoFactoryDefault::$buildInTypes)
     * @param bool              $isArray the value should be array of type $type or not
     * @param mixed             $value   scalar(builtin) or array of scalar values
     * @throws \microapi\dto\DtoFieldTypeMismatched
     * @see \microapi\dto\DtoFactoryDefault::$buildInTypes
     */
    protected function builtin(DTO $obj, string $field, string $type, bool $isArray, $value) {
        if ($isArray && !is_array($value)) {
            $class = get_class($obj);
            throw new DtoFieldTypeMismatched("{$class} expects array for field `{$field}`` but got scalar or object");
        }

        $obj->{$field} = $value;
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param \microapi\dto\DTO $obj     DTO object
     * @param string            $field   field name on object
     * @param string            $class   class of the field
     * @param bool              $isArray should be array
     * @param mixed             $value
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    protected function dtoObject(DTO $obj, string $field, string $class, bool $isArray, $value) {
        if ($isArray && !is_array($value)) {
            $class = get_class($obj);
            throw new DtoFieldTypeMismatched("{$class} expects array for field `{$field}`` but got scalar or object");
        }

        if ($isArray) {
            $res = [];
            foreach ($value as $item) {
                $res[] = $this->createFromData($class, $item);
            }
            $obj->{$field} = $res;
        }
        else {
            $obj->{$field} = $this->createFromData($class, $value);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param \microapi\dto\DTO $obj     DTO object
     * @param string            $field   field name on object
     * @param string            $class   class of the field
     * @param bool              $isArray should be array
     * @param mixed             $value
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    protected function generalObject(DTO $obj, string $field, string $class, bool $isArray, $value) {
        if ($isArray && !is_array($value)) {
            $class = get_class($obj);
            throw new DtoFieldTypeMismatched("{$class} expects array for field `{$field}`` but got scalar or object");
        }

        if ($isArray) {
            $res = [];
            foreach ($value as $item) {
                $res[] = new $class($item);
            }
            $obj->{$field} = $res;
        }
        else {
            $obj->{$field} = new $class($value);
        }
    }

    /**
     * @param \microapi\dto\DTO $obj
     * @param array             $fields
     * @param                   $meta
     * @param                   $name
     */
    public function fillFeild(DTO $obj, array $fields, $meta, $name) {
        if ($meta['type'] === null) {
            $class = get_class($obj);
            throw new DtoFieldExposingException("In class '{$class}', field '{$name}' annotated as @exposed but type is not specified");
        }

        if ($meta['builtin']) {
            $this->builtin($obj, $name, $meta['type'], $meta['isArray'], $fields[$name]);
        }
        else {
            if ($meta['isDto']) {
                // we support only nested DTO
                $this->dtoObject($obj, $name, $meta['type'], $meta['isArray'], $fields[$name]);
            }
            else {
                $this->generalObject($obj, $name, $meta['type'], $meta['isArray'], $fields[$name]);
            }
        }
    }
}
