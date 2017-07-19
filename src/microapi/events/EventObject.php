<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:30
 */

declare(strict_types=1);

namespace microapi\events;

class EventObject {
    private $_success = false;

    private $eventName;

    public function setFailed() { $this->_success = false; }

    public function isSuccess(): bool { return $this->_success; }
}