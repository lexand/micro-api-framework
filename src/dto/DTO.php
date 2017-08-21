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
 * All fields which will be filled from RAW POST JSON
 * - should be public
 * - almost read-only (only if you want predictable behavior). You should accept and support this freedom by yourself.
 *
 * Class DTO
 *
 * @package microapi\dto
 * @see     \microapi\dto\DtoFactory
 * @see     \microapi\dto\DtoFactoryDefault
 */
abstract class DTO {

    private $_errors;

    /**
     * !WARNING:
     * Constructor doesn't check types of values !!!
     *
     * @param array $fields
     */
    public function __construct(array $fields = []) {
        $props = get_object_vars($this);

        foreach ($props as $name => $defauls) {
            if (array_key_exists($name, $fields)) {
                $this->{$name} = $fields[$name];
            }
        }
    }

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
