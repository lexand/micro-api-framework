<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 14:04
 */

declare(strict_types = 1);

namespace microapi\base;

class Repository {

    private $_c;

    /**
     * @param string $name
     * @param mixed  $mixed data (object, array etc) ot callable/Closure for lazy component instantiation
     * @return $this
     */
    public function add(string $name, $mixed) {
        $this->_c[$name] = $mixed;

        return $this;
    }

    public function get(string $name) {
        if (array_key_exists($name, $this->_c)) {
            if (is_callable($this->_c[$name])) {
                // lazy instantiation
                $this->_c[$name] = call_user_func($this->_c[$name]);
            }

            return $this->_c[$name];
        }

        throw new RepositoryException("Object '{$name}' not set'");
    }

}

class RepositoryException extends \RuntimeException {

}