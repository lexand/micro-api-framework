<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:37
 */

declare(strict_types=1);

namespace microapi\endpoint;

use microapi\Controller;

class Endpoint {
    /**
     * @var \microapi\Controller
     */
    private $controller;
    /**
     * @var string
     */
    private $httpMethod;

    /**
     * @var string
     */
    private $uri;

    private $actionMeta = [];

    /**
     * Endpoint constructor.
     *
     * @param string               $httpMethod HTTP method
     * @param \microapi\Controller $controller
     * @param array                $actionMeta
     */
    public function __construct(string $httpMethod,
                                \microapi\Controller $controller,
                                array $actionMeta) {

        $this->controller   = $controller;
        $this->actionMeta   = $actionMeta;
        $this->httpMethod   = $httpMethod;
    }

    /**
     * @return \microapi\Controller
     */
    public function getController(): \microapi\Controller { return $this->controller; }

    /**
     * @return string
     */
    public function getActionMethod(): string { return $this->actionMeta['methodName']; }

    public function getActionName(): string { return strtolower(substr($this->actionMeta['methodName'], 6)); }

    /**
     * @return array
     */
    public function getParamsMeta(): array { return $this->actionMeta['paramsMeta']; }

    /**
     * @return string
     */
    public function getHttpMethod(): string { return $this->httpMethod; }

    /**
     * @return string
     */
    public function getUri(): string { return $this->uri; }

    public function invoke(array $params = []) {

        if ($this->controller->beforeAction($this->getActionName(), $params)) {
            if ($params === []) {
                $res = call_user_func([$this->controller, $this->actionMeta['methodName']]);
            }
            else {
                $res = call_user_func_array([$this->controller, $this->actionMeta['methodName']], $params);
            }

            return $this->controller->afterAction($this->getActionName(), $res);
        }
        throw new EndpointInvokeRejectedException();
    }
}
