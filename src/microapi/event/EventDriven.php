<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:28
 */

namespace microapi\event;

interface EventDriven {
    /**
     * @param string $event
     * @param callable[]  $f
     * @return \microapi\event\EventDriven
     */
    public function on(string $event, array $f) : EventDriven;

    /**
     * @param string                $event
     * @param \microapi\event\Event $e
     * @return \microapi\event\Event
     */
    public function trigger(string $event, Event $e) : Event;
}