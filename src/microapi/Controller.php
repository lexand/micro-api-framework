<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 22.09.16
 * Time: 16:18
 */

declare(strict_types=1);

namespace microapi;

use microapi\event\EventDriven;
use microapi\event\Events;
use microapi\event\object\AfterAction;
use microapi\event\object\BeforeAction;

class Controller implements EventDriven {

    use Events;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    protected $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

    /**
     * @param string $action
     * @param array  $params
     * @return bool
     */
    public function beforeAction(string $action, array $params = []): bool {
        return !$this->trigger('beforeaction', new BeforeAction($this, $action, $params))->isStopped();
    }

    public function afterAction(string $action, $res) {
        $responseEvent = new AfterAction($this, $action, $res);

        $this->trigger('afteraction', $responseEvent);

        return $responseEvent->response;
    }

    /**
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): \Psr\Http\Message\ServerRequestInterface {
        return $this->request;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @return static
     */
    public function setRequest(\Psr\Http\Message\ServerRequestInterface $request) {
        $this->request = $request;

        return $this;
    }

    /**
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getResponse(): \Psr\Http\Message\ResponseInterface {
        return $this->response;
    }

    /**
     * @param \Psr\Http\Message\ResponseInterface $response
     * @return static
     */
    public function setResponse(\Psr\Http\Message\ResponseInterface $response) {
        $this->response = $response;

        return $this;
    }
}
