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
use microapi\http\WrappedResponse;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

class Endpoint {
    /**
     * @var \microapi\Controller
     */
    private $controller;
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * @var string
     */
    private $uri;

    private $actionMeta;

    /**
     * Endpoint constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request HTTP method
     * @param \microapi\Controller                     $controller
     * @param array                                    $actionMeta
     */
    public function __construct(ServerRequestInterface $request,
                                Controller $controller,
                                array $actionMeta) {

        $this->controller = $controller;
        $this->actionMeta = $actionMeta;
        $this->request    = $request;
    }

    public function getController(): Controller { return $this->controller; }

    public function getActionMethod(): string { return $this->actionMeta['methodName']; }

    public function getActionName(): string { return strtolower(substr($this->actionMeta['methodName'], 6)); }

    public function getParamsMeta(): array { return $this->actionMeta['paramsMeta']; }

    public function getRequest(): ServerRequestInterface { return $this->request; }

    public function getUri(): string { return $this->uri; }

    /**
     * - create controller/action state (future HTTP response)
     * - call beforeAction/action/afterAction
     * - combine action result, initial request and response into \microapi\http\WrappedResponse
     *
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param array                               $params
     * @return \microapi\http\WrappedResponse
     */
    public function invoke(ResponseInterface $response, array $params = []): WrappedResponse {
        $this->controller->setRequest($this->request);
        $this->controller->setResponse($response);

        $this->controller->beforeAction($this->getActionName(), $params);

        $res = \call_user_func_array([$this->controller, $this->actionMeta['methodName']], $params);

        $res = $this->controller->afterAction($this->getActionName(), $res);

        return new WrappedResponse($this->request, $res, $this->controller->getResponse());
    }
}
