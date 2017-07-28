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
use Psr\Http\Message\ServerRequestInterface;

class BeforeDispatch extends Event {
    /**
     * @var ServerRequestInterface
     */
    public $request;
    /**
     * @var \microapi\endpoint\Endpoint
     */
    public $endpoint;

    /**
     *  constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \microapi\endpoint\Endpoint              $endpoint
     */
    public function __construct(ServerRequestInterface $request, Endpoint $endpoint) {
        $this->endpoint = $endpoint;
        $this->request  = $request;
    }
}
