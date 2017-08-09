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
use microapi\http\WrappedResponse;

class AfterDispatch extends Event {
    /**
     * @var \microapi\http\WrappedResponse
     */
    public $wr;

    /**
     *  constructor.
     *
     * @param WrappedResponse $data
     */
    public function __construct(WrappedResponse $data) { $this->wr = $data; }
}
