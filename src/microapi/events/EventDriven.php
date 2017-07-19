<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:28
 */

namespace microapi\events;

interface EventDriven {
    public function on(string $event, callable $f) : EventDriven;

    public function trigger(string $event) : EventObject;
}