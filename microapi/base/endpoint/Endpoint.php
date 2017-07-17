<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:37
 */

declare(strict_types=1);

namespace microapi\base\endpoint;

class Endpoint {
    /**
     * @var \microapi\base\Controller
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

    private $actionParams = [];

    /**
     * Endpoint constructor.
     *
     * @param string                    $httpMethod HTTP method
     * @param \microapi\base\Controller $controller
     * @param string                    $actionMethod
     * @param array                     $actionParams
     */
    public function __construct(string $httpMethod,
                                \microapi\base\Controller $controller,
                                string $actionMethod,
                                array $actionParams) {
        $this->controller   = $controller;
        $this->actionMethod = $actionMethod;
        $this->actionParams = $actionParams;
        $this->httpMethod   = $httpMethod;
    }

    /**
     * @return \microapi\base\Controller
     */
    public function getController(): \microapi\base\Controller { return $this->controller; }

    /**
     * @return string
     */
    public function getActionMethod(): string { return $this->actionMethod; }

    /**
     * @return array
     */
    public function getActionParams(): array { return $this->actionParams; }

    /**
     * @return string
     */
    public function getHttpMethod(): string { return $this->httpMethod; }

    /**
     * @return string
     */
    public function getUri(): string { return $this->uri; }

    public function call(array $params = []) {
        if ($params === []) {
            return call_user_func([$this->controller, $this->actionMethod]);
        }

        return call_user_func_array([$this->controller, $this->actionMethod], $params);
    }
}
