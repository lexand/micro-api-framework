<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 12:54
 */

declare(strict_types=1);

namespace microapi;

use GuzzleHttp\Psr7\ServerRequest;
use microapi\dto\DtoFactory;
use microapi\dto\DtoFactoryDefault;
use microapi\endpoint\Endpoint;
use microapi\endpoint\exceptions\EndpointActionNotFoundException;
use microapi\endpoint\exceptions\EndpointControllerNotFoundException;
use microapi\endpoint\exceptions\EndpointException;
use microapi\endpoint\Reflection;
use microapi\event\EventDriven;
use microapi\event\Events;
use microapi\event\object\AfterDispatch;
use microapi\event\object\BeforeDispatch;
use microapi\http\DefaultResponseFactory;
use microapi\http\HttpException;
use microapi\http\ResponseFactory;
use microapi\http\WrappedResponse;
use microapi\util\Tokenizer;
use microapi\util\Type;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

class Dispatcher implements EventDriven {

    use Events;

    /**
     * @var Dispatcher
     */
    private static $instance;

    /**
     * @var string[]
     */
    private $modules = [];

    /**
     * @var string
     */
    private $endpointCachePath;

    private $endPointCache = [];

    /**
     * @var int
     */
    private $skipPathComponents = 0;

    /**
     * @var bool
     */
    private $reflectionAllowed = true;
    /**
     * @var DtoFactory
     */
    private $dtoFactory;

    /**
     * @var \microapi\http\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var string[]
     */
    private $defaultControllers;
    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    private $request;

    /**
     * App constructor.
     *
     * @param \Psr\Http\Message\ServerRequestInterface|null $request
     */
    public function __construct(ServerRequestInterface $request = null) {
        if (static::$instance !== null) {
            throw new \LogicException('only one instance allowed');
        }

        if ($request === null) {
            $request = ServerRequest::fromGlobals();
        }
        $this->request = $request;
        $this->init();
    }

    /**
     * - all controllers in module should extends \microapi\Controller
     * - all controllers should placed under \&lt;module_namespace&gt;\controller namespace
     *
     * @param string $module module name
     * @param string $ns     module namespace.
     * @param string $defaultController
     *
     * @return $this
     */
    public function addModule(string $module, string $ns, string $defaultController = null): Dispatcher {
        $this->modules[$module] = $ns;

        if (!empty($defaultController)) {
            $this->defaultControllers[$module] = $defaultController;
        }

        return $this;
    }

    /**
     * - all controllers in module should extends \microapi\Controller
     * - all controllers should placed under \&lt;module_namespace&gt;\controller namespace
     *
     * @param string $ns
     * @param string $defaultController
     *
     * @return $this
     */
    public function addDefaultModule(string $ns, string $defaultController = null): Dispatcher {
        $this->addModule('__default', $ns, $defaultController);

        return $this;
    }

    /**
     * @param string $cachePath
     *
     * @return Dispatcher
     */
    public function setEndpointCachePath(string $cachePath): Dispatcher {
        $this->endpointCachePath = $cachePath;

        return $this;
    }

    /**
     * If reflection not allowed and you add new actions into controllers without rebuilding endpoints-cache, then
     * theses actions will not be visible to the Dispatcher
     *
     * @param bool $reflationAllowed
     *
     * @return Dispatcher
     */
    public function setReflectionAllowed(bool $reflationAllowed): Dispatcher {
        $this->reflectionAllowed = $reflationAllowed;

        return $this;
    }

    /**
     * - plugins support
     * - init plugins
     */
    public function init(): void { $this->trigger('init'); }

    /**
     * Perform real request
     *
     * @throws HttpException
     * @throws \microapi\endpoint\exceptions\EndpointActionNotFoundException
     * @throws \microapi\endpoint\exceptions\EndpointControllerNotFoundException
     */
    public function dispatch(): void {
        try {
            $uri       = $this->request->getUri();
            $tokenizer = new Tokenizer($uri->getPath(), '/', $this->skipPathComponents);

            $endpoint = $this->getEndpoint($tokenizer, $this->request);

            if ($endpoint === null) {
                throw new HttpException((string)$uri, HttpException::NOT_FOUND);
            }

            $this->beforeDispatch($this->request, $endpoint);

            $params = $this->extractEndpointParams($tokenizer, $this->request->getBody(), $endpoint);

            $this->afterDispatch(
                $endpoint->invoke(
                    $this->getResponseFactory()->create(),
                    $params
                )
            );
        }
        catch (\Throwable $t) {
            $this->afterDispatch(new WrappedResponse($this->request, $t));
        }
    }

    public static function get(ServerRequestInterface $request = null): Dispatcher {
        if (static::$instance === null) {
            static::$instance = new Dispatcher($request);
        }

        return static::$instance;
    }

    /**
     * @param int $skipPathComponents
     *
     * @return Dispatcher
     */
    public function setSkipPathComponents(int $skipPathComponents): Dispatcher {
        $this->skipPathComponents = $skipPathComponents;

        return $this;
    }

    /**
     * - plugins support
     * - init controller event listeners/handlers
     *
     * If request should not be processed event handler should throw HttpException
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \microapi\endpoint\Endpoint              $endpoint
     */
    private function beforeDispatch(ServerRequestInterface $request, Endpoint $endpoint): void {
        $this->trigger('beforedispatch', new BeforeDispatch($request, $endpoint));
    }

    /**
     * - plugins support
     * - decorate action result
     * - decorate errors/exceptions
     * - render response
     *
     * @param \microapi\http\WrappedResponse $data
     */
    private function afterDispatch(WrappedResponse $data): void {
        $this->trigger('afterdispatch', new AfterDispatch($data));
    }

    /**
     * @param \microapi\util\Tokenizer                 $tokenizer
     * @param \Psr\Http\Message\ServerRequestInterface $request
     *
     * @return \microapi\endpoint\Endpoint|null
     * @internal
     */
    public function getEndpoint(Tokenizer $tokenizer, ServerRequestInterface $request): ?Endpoint {
        // module or controller name
        $module = $tokenizer->next();
        if ($module !== null) {
            if (isset($this->modules[$module])) {
                $fqcnCtl = $this->ctlFqcn($module, $tokenizer->next());
            }
            else {
                $fqcnCtl = $this->ctlFqcn('__default', $module);
            }
        }
        else {
            $fqcnCtl = $this->ctlFqcn('__default');
        }

        $actionName = $tokenizer->next() ?? 'index';

        $endpoint = $this->getEndpointFromCache($request, $fqcnCtl, $actionName);
        if ($endpoint === null) {
            $endpoint = $this->getEndpointFromReflection($request, $fqcnCtl, $actionName);
        }

        return $endpoint;
    }

    /**
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $fqcnCtl
     * @param string                                   $action
     *
     * @return \microapi\endpoint\Endpoint|null
     * @throws \microapi\endpoint\exceptions\EndpointControllerNotFoundException
     * @throws \microapi\endpoint\exceptions\EndpointActionNotFoundException
     * @internal
     */
    public function getEndpointFromCache(ServerRequestInterface $request, string $fqcnCtl, string $action): ?Endpoint {
        $method = \strtolower($request->getMethod());
        if (empty($this->endPointCache[$method])) {
            $this->loadEndpointCache($method);
        }

        if (isset($this->endPointCache[$method])) {
            $controllers = $this->endPointCache[$method];
            if (isset($controllers[$fqcnCtl]) && \class_exists($fqcnCtl)) {
                $actions = $controllers[$fqcnCtl];
                if (isset($actions[$action])) {
                    return new Endpoint(
                        $request,
                        new $fqcnCtl(),
                        $actions[$action]
                    );
                }
                throw new EndpointActionNotFoundException("'{$action}' not found in '{$fqcnCtl}'");
            }
            throw new EndpointControllerNotFoundException("'{$fqcnCtl}' not found");
        }

        return null;
    }

    private function loadEndpointCache(string $method): void {
        $filename = $this->endpointCachePath . "/endpoints_{$method}.php";
        if (\file_exists($filename)) {
            $this->endPointCache[$method] = require $filename;
        }
    }

    /**
     * @param ServerRequestInterface $request
     * @param string                 $fqcnCtl
     * @param string                 $action
     *
     * @return \microapi\endpoint\Endpoint|null
     * @throws \microapi\http\HttpException
     * @internal
     */
    public function getEndpointFromReflection(ServerRequestInterface $request,
                                              string $fqcnCtl,
                                              string $action
    ): ?Endpoint {
        if ($this->reflectionAllowed) {
            try {
                return (new Reflection($request, $fqcnCtl, $action))->getEndpoint();
            }
            catch (EndpointException $e) {
                throw new HttpException($e->getMessage(), HttpException::NOT_FOUND);
            }
        }

        return null;
    }

    /**
     * @param \microapi\util\Tokenizer          $tokenizer
     * @param \Psr\Http\Message\StreamInterface $stream
     * @param \microapi\endpoint\Endpoint       $endpoint
     *
     * @return array
     */
    public function extractEndpointParams(Tokenizer $tokenizer, StreamInterface $stream, Endpoint $endpoint): array {
        // prepare arguments and DTO
        $paramsMeta = $endpoint->getParamsMeta();
        $params     = [];
        foreach ($paramsMeta as $paramName => $meta) {
            if ($meta['builtin']) {
                $val = $tokenizer->next();
                if (($val === null) && !$meta['optional']) {
                    $params[$paramName] = $meta['default'];
                }
                else {
                    $params[$paramName] = Type::cast($meta['type'], $val);
                }
            }
            else {
                $params[$paramName] = $this->dtoFactory()->createFromStream($meta['type'], $stream);
            }
        }

        return $params;
    }

    private function dtoFactory(): DtoFactory {
        if ($this->dtoFactory === null) {
            $this->dtoFactory = new DtoFactoryDefault();
        }

        return $this->dtoFactory;
    }

    public function setDtoFactory(DtoFactory $dtoFactory): Dispatcher {
        $this->dtoFactory = $dtoFactory;

        return $this;
    }

    /**
     * @return \microapi\http\ResponseFactory
     */
    public function getResponseFactory(): ResponseFactory {
        if ($this->responseFactory === null) {
            $this->responseFactory = new DefaultResponseFactory();
        }

        return $this->responseFactory;
    }

    /**
     * @param \microapi\http\ResponseFactory $responseFactory
     *
     * @return static
     */
    public function setResponseFactory(ResponseFactory $responseFactory): Dispatcher {
        $this->responseFactory = $responseFactory;

        return $this;
    }

    /**
     * @param string      $module
     * @param string|null $ctlName
     *
     * @return string
     * @throws \LogicException
     */
    public function ctlFqcn(string $module, string $ctlName = null): string {
        if ($ctlName === null) {
            if (!isset($this->defaultControllers[$module])) {
                throw new \LogicException(
                    "You request default controller from '{$module}' but default controller not specified"
                );
            }
            $ctlName = \ucfirst($this->defaultControllers[$module]);
        }

        return $this->modules[$module] . '\controller\\' . \ucfirst($ctlName) . 'Ctl';
    }
}
