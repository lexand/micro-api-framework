<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 12:54
 */

declare(strict_types=1);

namespace microapi;

use microapi\dto\DtoFactory;
use microapi\dto\DtoFactoryDefault;
use microapi\endpoint\Endpoint;
use microapi\endpoint\exceptions\EndpointActionNotFoundException;
use microapi\endpoint\exceptions\EndpointControllerNotFoundException;
use microapi\endpoint\exceptions\EndpointException;
use microapi\endpoint\exceptions\EndpointInvokeRejectedException;
use microapi\endpoint\Reflection;
use microapi\event\EventDriven;
use microapi\event\object\AfterDispatch;
use microapi\event\object\BeforeDispatch;
use microapi\event\Events;
use microapi\http\HttpException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Dispatcher implements EventDriven {

    use Events;

    /**
     * @var string
     */
    private $uri;
    /**
     * @var string
     */
    private $method;
    /**
     * @var array
     */
    private $uriComponents;
    /**
     * @var Dispatcher
     */
    private static $instance;

    private $modulesNamespaces = [];

    private $endpointCachePath = null;

    private $endPointCache = [];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $skipPathComponents = 0;

    private $reflationAllowed = true;
    /**
     * @var DtoFactory
     */
    private $dtoFactory;

    /**
     * App constructor.
     *
     * @throws \LogicException
     */
    public function __construct() {
        if (static::$instance !== null) {
            throw new \LogicException('only one instance allowed');
        }

        $this->init();
    }

    /**
     * - all controllers in module should extends \microapi\Controller
     * - all controllers should placed under \&lt;module_namespace&gt;\controller namespace
     *
     * @param string $module module name
     * @param string $ns     module namespace.
     * @return $this
     */
    public function addModule(string $module, string $ns) {
        $this->modulesNamespaces[$module] = $ns;

        return $this;
    }

    /**
     * - all controllers in module should extends \microapi\Controller
     * - all controllers should placed under \&lt;module_namespace&gt;\controller namespace
     *
     * @param string $ns
     * @return $this
     */
    public function addDefaultModule(string $ns) {
        $this->modulesNamespaces['__default'] = $ns;

        return $this;
    }

    /**
     * @param string $cachePath
     * @return Dispatcher
     */
    public function setEndpointCachePath(string $cachePath): Dispatcher {
        $this->endpointCachePath = $cachePath;

        return $this;
    }

    /**
     * @param bool $reflationAllowed
     * @return Dispatcher
     */
    public function setReflationAllowed(bool $reflationAllowed): Dispatcher {
        $this->reflationAllowed = $reflationAllowed;

        return $this;
    }


    public function init() {
        set_exception_handler([$this, 'exceptionHandler']);
    }

    private static function castType($value, $buildInType) {
        switch ($buildInType) {
            case 'string':
                return (string)$value;
            case 'int':
                return (int)$value;
            case 'float':
                return (float)$value;
            case 'double':
                return (double)$value;
            case 'bool':
                return (bool)$value;
            default:
                throw new \LogicException('incorrect type');
        }
    }

    public function exceptionHandler(\Throwable $t) {

        $this->log()->critical($t->getMessage(), ['server' => $_SERVER, 'trace' => $t->getTrace()]);

        $this->response(
            [
                'error'     => true,
                'errorMsg'  => $t->getMessage(),
                'errorCode' => $t->getCode()
            ]
        );
    }

    /**
     * Perform real request
     *
     * @throws HttpException
     */
    public function dispatch() {
        $this->preDispatch();

        try {
            $endpoint = $this->getEndpoint();

            if ($endpoint === null) {
                throw new HttpException($_SERVER['REQUEST_URI'], HttpException::NOT_FOUND);
            }

            if (!$this->beforeDispatch($endpoint)) {
                throw new HttpException('request rejected', HttpException::SERVER_ERROR);
            }

            $params = $this->extractEndpointParams($endpoint);

            try {
                $this->afterDispatch($endpoint->invoke($params));
            }
            catch (EndpointInvokeRejectedException $e) {
                throw new HttpException('request denied', HttpException::FORBIDDEN);
            }
        }
        catch (\Throwable $t) {
            $this->afterDispatch($t);
        }
    }

    /**
     * @param array|\JsonSerializable $data
     * @param int                     $code
     */
    public function response($data, int $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE, JSON_PRESERVE_ZERO_FRACTION);
    }

    /**
     * @return string
     */
    private function getRaw(): string {
        $fh      = fopen('php://input', 'b');
        $rawPost = fgets($fh);
        fclose($fh);

        if ($rawPost === false) {
            return '';
        }

        return $rawPost;
    }

    /**
     * @return string|null
     */
    public function getNextUriComponent() {
        if (empty($this->uriComponents)) {
            return null;
        }

        return array_shift($this->uriComponents);
    }

    public static function get() {
        if (static::$instance === null) {
            static::$instance = new Dispatcher();
        }

        return static::$instance;
    }

    public function log(): \Psr\Log\LoggerInterface {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    public function setLog(LoggerInterface $li): Dispatcher {
        $this->logger = $li;

        return $this;
    }

    /**
     * @internal
     */
    public function preDispatch() {
        $this->uri    = $_SERVER['REQUEST_URI'];
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $parsedUrl = parse_url($this->uri);
        $path      = ltrim($parsedUrl['path'], '//');
        // skip several components of the URI path, for example /api etc
        $this->uriComponents = array_slice(explode('/', $path), $this->skipPathComponents);
    }

    /**
     * @param int $skipPathComponents
     * @return Dispatcher
     */
    public function setSkipPathComponents(int $skipPathComponents): Dispatcher {
        $this->skipPathComponents = $skipPathComponents;

        return $this;
    }

    private function beforeDispatch(Endpoint $endpoint): bool {
        return !$this->trigger('beforedispatch', new BeforeDispatch($this->uri, $endpoint))->isStopped();
    }

    private function afterDispatch($data) {
        $this->trigger('afterdispatch', new AfterDispatch($data));
    }

    /**
     * @return \microapi\endpoint\Endpoint|null
     * @throws \microapi\http\HttpException
     * @throws \microapi\endpoint\exceptions\EndpointControllerNotFoundException
     * @throws \microapi\endpoint\exceptions\EndpointActionNotFoundException
     * @internal
     */
    public function getEndpoint() {
        // module or controller name
        $part = $this->getNextUriComponent();
        if (isset($this->modulesNamespaces[$part])) {
            $ctlName = $this->getNextUriComponent();
            $fqcnCtl = $this->modulesNamespaces[$part] . '\controller\\' . ucfirst($ctlName) . 'Ctl';
        }
        else {
            $fqcnCtl = $this->modulesNamespaces['__default'] . '\controller\\' . ucfirst($part) . 'Ctl';
        }

        $actionName = $this->getNextUriComponent();

        $endpoint = $this->getEndpointFromCache($this->method, $fqcnCtl, $actionName);
        if ($endpoint === null) {
            $endpoint = $this->getEndpointFromReflection($this->method, $fqcnCtl, $actionName);
        }

        return $endpoint;
    }

    /**
     * @param string $method
     * @param string $fqcnCtl
     * @param string $action
     * @return \microapi\endpoint\Endpoint|null
     * @throws \microapi\endpoint\exceptions\EndpointControllerNotFoundException
     * @throws \microapi\endpoint\exceptions\EndpointActionNotFoundException
     * @internal
     */
    public function getEndpointFromCache(string $method, string $fqcnCtl, string $action) {
        if (empty($this->endPointCache[$method])) {
            $this->loadEndpointCache($method);
        }

        if (isset($this->endPointCache[$method])) {
            $controllers = $this->endPointCache[$method];
            if (isset($controllers[$fqcnCtl]) && class_exists($fqcnCtl)) {
                $actions = $controllers[$fqcnCtl];
                if (isset($actions[$action])) {
                    return new Endpoint(
                        $method,
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

    private function loadEndpointCache(string $method) {
        $filename = $this->endpointCachePath . "/endpoints_{$method}.php";
        if (file_exists($filename)) {
            $this->endPointCache[$method] = require $filename;
        }
    }

    /**
     * @param string $method
     * @param string $fqcnCtl
     * @param string $action
     * @return \microapi\endpoint\Endpoint|null
     * @throws \microapi\http\HttpException
     * @internal
     */
    public function getEndpointFromReflection(string $method, string $fqcnCtl, string $action) {
        if ($this->reflationAllowed) {
            try {
                return (new Reflection($method, $fqcnCtl, $action))->getEndpoint();
            }
            catch (EndpointException $e) {
                throw new HttpException($e->getMessage(), HttpException::NOT_FOUND);
            }
        }

        return null;
    }

    /**
     * @param \microapi\endpoint\Endpoint $endpoint
     * @return array
     * @throws \microapi\http\HttpException
     */
    public function extractEndpointParams(Endpoint $endpoint): array {
        // prepare arguments and DTO
        $paramsMeta = $endpoint->getParamsMeta();
        $params     = [];
        foreach ($paramsMeta as $paramName => $meta) {
            if ($meta['builtin']) {
                $val = $this->getNextUriComponent();
                if (($val === null) && !$meta['optional']) {
                    $params[$paramName] = $meta['default'];
                }
                else {
                    $params[$paramName] = self::castType($val, $meta['type']);
                }
            }
            else {
                $params[$paramName] = $this->dtoFactory()->create($meta['type'], $this->getRaw());
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

    /**
     * @param \microapi\dto\DtoFactory $dtoFactory
     * @return Dispatcher
     */
    public function setDtoFactory(\microapi\dto\DtoFactory $dtoFactory): Dispatcher {
        $this->dtoFactory = $dtoFactory;

        return $this;
    }


}
