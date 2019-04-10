<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:39
 */

declare(strict_types=1);

namespace microapi\dto;

use microapi\util\Type;
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
 * - DTO supports fields with arrayed type (array of scalars/objects)
 *
 *
 * @package microapi\dto
 * @see     \microapi\dto\DTO
 */
class DtoFactoryDefault implements DtoFactory {

    use DtoTypeAnnotationTrait;

    private $_c = [];

    /**
     * @var string
     */
    private $cachePath;

    public function createFromStream(string $class, StreamInterface $stream): DTO {
        $fields = \json_decode($stream->getContents(), true);

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
     *
     * @throws \microapi\dto\DtoFieldExposingException
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    public function fillObjViaReflection(DTO $obj, array $fields): void {
        $r     = new \ReflectionObject($obj);
        $props = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
        foreach ($props as $prop) {
            $name = $prop->getName();
            if (isset($fields[$name])) {
                $meta = self::annotatedMeta($prop->getDocComment());

                if (!$meta['exposed']) {
                    continue;
                }

                $this->fillField($obj, $fields, $meta, $name);
            }
        }
    }

    public function fillObjViaMeta(DTO $obj, array $fields, array $fieldsMeta): void {
        foreach ($fieldsMeta as $name => $meta) {
            if (isset($fields[$name])) {
                $this->fillField($obj, $fields, $meta, $name);
            }
        }
    }

    /**
     * @param string $class
     *
     * @return array
     * @see \microapi\dto\CacheBuilder for details
     */
    public function metaFor(string $class): array {
        if (!isset($this->_c[$class])) {

            $file = $this->cachePath . '/';
            $file .= \str_replace('\\', '_', \trim($class, '\\')) . '.php';

            if (\file_exists($file)) {
                $this->_c[$class] = require $file;
            }
            else {
                $this->_c[$class] = [];
            }
        }

        return $this->_c[$class];
    }

    public function setCachePath(string $path): DtoFactoryDefault {
        $this->cachePath = $path;

        return $this;
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
     *
     * @throws \microapi\dto\DtoFieldTypeMismatched
     * @see \microapi\dto\DtoFactoryDefault::$buildInTypes
     */
    protected function builtin(DTO $obj, string $field, string $type, bool $isArray, $value): void {
        if ($isArray && !\is_array($value)) {
            $class = \get_class($obj);
            throw new DtoFieldTypeMismatched("{$class} expects array for field `{$field}`` but got scalar or object");
        }

        if ($isArray) {
            $res = [];
            foreach ($value as $item) {
                $res[] = Type::cast($type, $item);
            }
            $obj->{$field} = $res;
        }
        else {
            $obj->{$field} = Type::cast($type, $value);
        }
    }

    /** @noinspection MoreThanThreeArgumentsInspection */
    /**
     * @param \microapi\dto\DTO $obj     DTO object
     * @param string            $field   field name on object
     * @param string            $class   class of the field
     * @param bool              $isArray should be array
     * @param mixed             $value
     *
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    protected function dtoObject(DTO $obj, string $field, string $class, bool $isArray, $value): void {
        if ($isArray && !\is_array($value)) {
            $class = \get_class($obj);
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
     *
     * @throws \microapi\dto\DtoFieldTypeMismatched
     */
    protected function generalObject(DTO $obj, string $field, string $class, bool $isArray, $value): void {
        if ($isArray && !\is_array($value)) {
            $class = \get_class($obj);
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
     *
     * @throws \microapi\dto\DtoFieldTypeMismatched
     * @throws \microapi\dto\DtoFieldExposingException
     */
    public function fillField(DTO $obj, array $fields, array $meta, string $name): void {
        if ($meta['type'] === null) {
            $class = \get_class($obj);
            throw new DtoFieldExposingException(
                "In class '{$class}', field '{$name}' annotated as @exposed but type is not specified"
            );
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
