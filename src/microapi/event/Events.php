<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:27
 */

namespace microapi\event;

trait Events {

    /**
     * @var callable[][]
     */
    private $_e = [];

    /**
     * add event listener/handler
     *
     * @param string     $event
     * @param callable[] $f
     * @return static
     */
    public function on(string $event, array $f){

        $event = strtolower($event);

        foreach ($f as $_f) {
            if (is_callable($_f)) {
                $this->_e[$event][] = $_f;
            }
        }

        return $this;
    }

    public function trigger(string $event, Event $ef = null): Event {
        $event = strtolower($event);

        if ($ef === null) {
            $ef = new Event();
        }

        if (isset($this->_e[$event])) {
            foreach ($this->_e[$event] as $func) {
                /** @var \microapi\event\Event $ef */
                $ef = $func($ef);
                if ($ef->isStopped()) {
                    break;
                }
            }
        }

        return $ef;
    }
}
