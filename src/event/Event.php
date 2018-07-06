<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 18:30
 */

declare(strict_types=1);

namespace microapi\event;

class Event {
    private $stopped = false;

    public function setStopped(): void { $this->stopped = true; }

    public function isStopped(): bool { return $this->stopped; }
}