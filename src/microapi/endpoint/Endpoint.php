<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:37
 */

declare(strict_types=1);

namespace microapi\endpoint;

use GuzzleHttp\Psr7\Response;
use microapi\Controller;
use microapi\endpoint\exceptions\EndpointInvokeRejectedException;
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

    private $actionMeta = [];

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

    /**
     * @return \microapi\Controller
     */
    public function getController(): Controller { return $this->controller; }

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
     * @return \Psr\Http\Message\ServerRequestInterface
     */
    public function getRequest(): ServerRequestInterface { return $this->request; }

    /**
     * @return string
     */
    public function getUri(): string { return $this->uri; }

    /**
     * @param array $params
     * @return array
     * @throws \microapi\endpoint\exceptions\EndpointInvokeRejectedException
     */
    public function invoke(array $params = []) {

        $this->controller->setRequest($this->request);
        if ($this->controller->beforeAction($this->getActionName(), $params)) {
            $this->controller->setResponse(new Response());
            if ($params === []) {
                $res = call_user_func([$this->controller, $this->actionMeta['methodName']]);
            }
            else {
                $res = call_user_func_array([$this->controller, $this->actionMeta['methodName']], $params);
            }

            // todo: pass request and response
            return $this->controller->afterAction($this->getActionName(), $res);
        }
        // todo: specify the reason (textual representation) of exception
        throw new EndpointInvokeRejectedException();
    }
}
