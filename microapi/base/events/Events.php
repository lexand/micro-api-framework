<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:27
 */

namespace microapi\base\events;

trait Events{

    /**
     * @var callable[][]
     */
    private $_e = [];

    public function on(string $event, callable $f): EventDriven {

        $event = strtolower($event);

        $this->_e[$event][] = $f;

        return $this;
    }

    public function trigger(string $event, EventObject $ef = null): EventObject {
        $event = strtolower($event);

        if ($ef === null) {
            $ef = new EventObject($event);
        }

        if (isset($this->_e[$event])) {
            foreach ($this->_e[$event] as $func) {
                $ef = $func($ef);
            }
        }

        return $ef;
    }
}
