<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 14:55
 */

declare(strict_types = 1);

namespace microapi\dto;

abstract class DTO {

    private $_errors;

    /**
     * DTO constructor.
     */
    public function __construct(array $fields) {
        $objFields = get_object_vars($this);

        $fields = array_intersect_key($fields, $objFields);

        foreach ($fields as $field => $value) {
            // здесь может и не понадобится приведение типов, так как данные будут передаватсья джсоном,
            // а в нем базовые типы сохраняются
            // но с другой стороны можно добавить поддержку вложенных объектов,
            // и тогда надо будет сделать приведение типов
            $this->{$field} = $value;
        }

    }

    /**
     * low-level validation
     * @return bool
     */
    public function validate() : bool { return true; }

    public function addError(string $field, string $error) {
        if (!isset($this->_errors[$field])) {
            $this->_errors[$field] = [];
        }

        $this->_errors[$field][] = $error;
    }

    public function getErrors(string $field = '') : array {
        if ($field === '') {
            return $this->_errors;
        }

        if (isset($this->_errors[$field])) {
            return $this->_errors[$field];
        }

        return [];
    }

}
