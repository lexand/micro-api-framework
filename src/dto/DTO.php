<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 14:55
 */

declare(strict_types=1);

namespace microapi\dto;

/**
 * Base class of Data Transfer Object pattern.
 *
 * But here is introduced next convention:
 *
 * All fields which will be filled from $fields _constructor argument
 * - should be public
 * - almost read-only (only if you want predictable behavior). You should accept and support this freedom by yourself.
 * - such fields should annotated with its type (builtin or class) and @exposed. Fields with @exposed and without type
 * will throw exception DtoFieldExposingException. Fields without @exposed will be meant as auxiliary.
 * - DTO class supports nesting of DTO and general objects. Nested DTO objects will instantiated with the same
 * DtoFactory. General objects will be created as "new $class($rawData)"
 * - DTO supports fields with arrayed type (array od scalars/objects)
 *
 * Class DTO
 *
 * @package microapi\dto
 * @see \microapi\dto\DtoFactory
 * @see \microapi\dto\DtoFactoryDefault
 */
abstract class DTO {

    private $_errors;

    /**
     * low-level validation
     *
     * @return bool
     */
    public function validate(): bool { return true; }

    public function addError(string $field, string $error) {
        if (!isset($this->_errors[$field])) {
            $this->_errors[$field] = [];
        }

        $this->_errors[$field][] = $error;
    }

    public function getErrors(string $field = ''): array {
        if ($field === '') {
            return $this->_errors;
        }

        if (isset($this->_errors[$field])) {
            return $this->_errors[$field];
        }

        return [];
    }
}
