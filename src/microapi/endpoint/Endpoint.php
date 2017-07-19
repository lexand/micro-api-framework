<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:37
 */

declare(strict_types=1);

namespace microapi\endpoint;

class Endpoint {
    /**
     * @var \microapi\Controller
     */
    private $controller;

    /**
     * @var string
     */
    private $actionMethod;

    /**
     * @var string
     */
    private $httpMethod;

    /**
     * @var string
     */
    private $uri;

    private $paramsMeta = [];

    /**
     * Endpoint constructor.
     *
     * @param string               $httpMethod HTTP method
     * @param \microapi\Controller $controller
     * @param string               $actionMethod
     * @param array                $paramsMeta
     */
    public function __construct(string $httpMethod,
                                \microapi\Controller $controller,
                                string $actionMethod,
                                array $paramsMeta) {
        $this->controller   = $controller;
        $this->actionMethod = $actionMethod;
        $this->paramsMeta   = $paramsMeta;
        $this->httpMethod   = $httpMethod;
    }

    /**
     * @return \microapi\Controller
     */
    public function getController(): \microapi\Controller { return $this->controller; }

    /**
     * @return string
     */
    public function getActionMethod(): string { return $this->actionMethod; }

    public function getActionName(): string { return strtolower(substr($this->actionMethod, 6)); }

    /**
     * @return array
     */
    public function getParamsMeta(): array { return $this->paramsMeta; }

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
                return call_user_func([$this->controller, $this->actionMethod]);
            }

            return call_user_func_array([$this->controller, $this->actionMethod], $params);
        }

        throw new EndpointCallRejectedException();
    }
}
