<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 12:54
 */

declare(strict_types=1);

namespace microapi;

use microapi\base\endpoint\Endpoint;
use microapi\base\endpoint\EndpointException;
use microapi\base\events\EventDriven;
use microapi\base\events\EventObject;
use microapi\base\events\Events;
use microapi\endpoint\Reflection;
use microapi\http\HttpException;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

class Dispatcher implements EventDriven {

    use Events;

    const METHOD_POST   = 'post';
    const METHOD_PUT    = 'put';
    const METHOD_GET    = 'get';
    const METHOD_DELETE = 'delete';

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
     * @var \microapi\Dispatcher
     */
    private static $instance;

    private $modulesNamespaces = [];

    private $endpointMapping = [];

    private $endpointCachePath = null;

    private $endPointCache = [];
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    private $skipPathComponents = 0;

    private $reflationAllowed = true;

    /**
     * App constructor.
     */
    public function __construct() {
        if (static::$instance !== null) {
            throw new \LogicException('only one instance allowed');
        }

        $this->init();
    }

    /**
     * all controllers should extends \microapi\base\Controller
     *
     * @param string $module
     * @param string $ns
     * @return $this
     */
    public function addModule(string $module, string $ns) {
        $this->modulesNamespaces[$module] = $ns;

        return $this;
    }

    /**
     * all controllers should extends \microapi\base\Controller
     *
     * @param string $ns
     * @return $this
     */
    public function addDefaultModule(string $ns) {
        $this->modulesNamespaces['__default'] = $ns;

        return $this;
    }

    public function setEndpointCachePath(string $cachePath) {
        $this->endpointCachePath = $cachePath;
    }

    /**
     * @param bool $reflationAllowed
     * @return \microapi\Dispatcher
     */
    public function setReflationAllowed(bool $reflationAllowed): \microapi\Dispatcher {
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

    public function dispatch() {
        $this->preDispatch();

        $endpoint = $this->getEndpoint();

        if (!$this->beforeDispatch($endpoint)) {
            throw new \microapi\http\HttpException('request rejected', 500);
        }

        if ($endpoint === null) {
            throw new \microapi\http\HttpException($_SERVER['REQUEST_URI'], 404);
        }

        // prepare arguments and DTO

        $this->afterDispatch($endpoint->call());
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

    public function getNextUriComponent() {
        if (empty($this->uriComponents)) {
            throw new \microapi\http\HttpException('incorrect uri');
        }

        return array_shift($this->uriComponents);
    }

    public function extractUrlParams(): array {
        $params = [];

        try {
            while (true) {
                $params[] = $this->getNextUriComponent();
            }
        }
        catch (\microapi\http\HttpException $ignored) {

        }

        return $params;
    }

    public static function get() {
        if (static::$instance === null) {
            static::$instance = new Dispatcher();
        }

        return static::$instance;
    }

    /**
     * HTTP method
     *
     * @return string
     */
    public function getMethod(): string { return $this->method; }

    public function log(): \Psr\Log\LoggerInterface {
        if ($this->logger === null) {
            $this->logger = new NullLogger();
        }

        return $this->logger;
    }

    public function setLog(LoggerInterface $li): \microapi\Dispatcher {
        $this->logger = $li;

        return $this;
    }

    protected function preDispatch() {
        $this->uri    = $_SERVER['REQUEST_URI'];
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $parsedUrl = parse_url($this->uri);
        $path      = ltrim($parsedUrl['path'], '//');
        // skip several components of the URI path, for example /api etc
        $this->uriComponents = array_slice(explode('/', $path), $this->skipPathComponents);
    }

    /**
     * @param int $skipPathComponents
     * @return \microapi\Dispatcher
     */
    public function setSkipPathComponents(int $skipPathComponents): \microapi\Dispatcher {
        $this->skipPathComponents = $skipPathComponents;

        return $this;
    }

    private function beforeDispatch(Endpoint $endpoint): true {
        $uri = $this->uri;

        return $this->trigger(
            'beforedispatch',
            new class($uri, $endpoint) extends EventObject {
                /**
                 * @var string
                 */
                public $uri;
                /**
                 * @var base\endpoint\Endpoint
                 */
                public $endpoint;

                /**
                 *  constructor.
                 *
                 * @param $endpoint
                 */
                public function __construct($uri, $endpoint) {
                    $this->endpoint = $endpoint;
                    $this->uri      = $uri;
                }

            }
        )
                    ->isSuccess();
    }

    private function afterDispatch(array $data) {
        $this->trigger(
            'afterdispath',
            new class ($data) extends EventObject {
                public $data = [];

                /**
                 *  constructor.
                 *
                 * @param array $data
                 */
                public function __construct(array $data) { $this->data = $data; }
            }
        );
    }

    private function getEndpoint() {
        // module or controller name
        $part = $this->getNextUriComponent();
        if (isset($this->modulesNamespaces[$part])) {
            $ctlName = $this->getNextUriComponent();
            $fqcnCtl = $this->modulesNamespaces[$part] . '/' . ucfirst($ctlName) . 'Ctl';
        }
        else {
            $fqcnCtl = $this->modulesNamespaces['__default'] . '/' . ucfirst($part) . 'Ctl';
        }

        $actionName = $this->getNextUriComponent();

        $endpoint = $this->getEndpointFromCache($this->method, $fqcnCtl, $actionName);
        if ($endpoint === null) {
            $endpoint = $this->getEndpointFromReflection($this->method, $fqcnCtl, $actionName);
        }

        return $endpoint;
    }

    private function getEndpointFromCache(string $method, string $fqcnCtl, string $action) {
        if (empty($this->endPointCache[$method])) {
            $this->loadEndpointCache($method);
        }

        $controllers = $this->endPointCache[$method];
        if (isset($controllers[$fqcnCtl])) {
            $actions = $controllers[$fqcnCtl];
            if (class_exists($fqcnCtl) && isset($actions[$action])) {
                return $actions[$action];
            }
        }

        return null;
    }

    private function loadEndpointCache(string $method) {
        $filename = $this->endpointCachePath . "/endpoints_{$method}.php";
        if (file_exists($filename)) {
            $this->endPointCache[$method] = require $filename;
        }
    }

    private function getEndpointFromReflection(string $method, string $fqcnCtl, string $action) {
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


}
