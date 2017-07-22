<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:26
 */

declare(strict_types=1);

namespace microapi\event\object;

use microapi\endpoint\Endpoint;
use microapi\event\Event;

class BeforeDispatch extends Event {
    /**
     * @var string
     */
    public $uri;
    /**
     * @var \microapi\endpoint\Endpoint
     */
    public $endpoint;

    /**
     *  constructor.
     *
     * @param $endpoint
     */
    public function __construct(string $uri, Endpoint $endpoint) {
        $this->endpoint = $endpoint;
        $this->uri      = $uri;
    }
}