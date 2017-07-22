<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:17
 */

declare(strict_types=1);

namespace microapi\event\object;

use microapi\event\Event;

class AfterDispatch extends Event {
    public $data = [];

    /**
     *  constructor.
     *
     * @param mixed $data
     */
    public function __construct($data) { $this->data = $data; }
}
