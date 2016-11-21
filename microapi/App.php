<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 12:54
 */

declare(strict_types = 1);

namespace microapi;

use microapi\base\Controller;
use microapi\base\DTO;
use microapi\base\Repository;
use microapi\base\RepositoryException;
use microapi\http\HttpException;
use ReflectionObject;

class App {
    const METHOD_POST          = 'post';
    const METHOD_PUT           = 'put';
    const METHOD_GET           = 'get';
    const SKIP_PATH_COMPONENTS = 1;

    /**
     * @var string
     */
    private $uri;
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $uriComponents;
    /**
     * @var \microapi\App
     */
    private static $instance;
    /**
     * @var \microapi\base\Repository
     */
    private $repository;

    /**
     * App constructor.
     */
    public function __construct() {
        if (static::$instance !== null) {
            throw new \LogicException('only one instance allowed');
        }

        $this->init();
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

        switch ($this->method) {
            case static::METHOD_POST:
            case static::METHOD_PUT:
                $this->dispatchPost();
                break;
            case static::METHOD_GET:
                $this->dispatchGet();
                break;
            default:
                throw new \microapi\http\HttpException('unsupported http method', 500);

        }
    }

    private function dispatchGet() {
        $controller = $this->getNextUriComponent();
        $action     = $this->getNextUriComponent();

        // параметры переданные в пути
        $urlParams = $this->extractUrlParams();

        $this->log()->debug('METHOD GET', ['controller' => $controller, 'action' => $action, 'params' => $urlParams]);

        $response = $this->callAction($controller, $action, $urlParams, []);

        $this->response($response);

    }

    private function dispatchPost() {
        $rawPost = $this->getRawPost();

        // todo: add check header - should be application/json

        $postData = json_decode($rawPost, true);

        $controller = $this->getNextUriComponent();
        $action     = $this->getNextUriComponent();

        // параметры переданные в пути
        $urlParams = $this->extractUrlParams();

        $this->log()
             ->debug(
                 'METHOD POST',
                 ['controller' => $controller, 'action' => $action, 'params' => $urlParams, 'post' => $postData]
             );

        $response = $this->callAction($controller, $action, $urlParams, $postData);

        $this->response($response);
    }

    public function response($data, int $code = 200) {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_UNESCAPED_UNICODE, JSON_PRESERVE_ZERO_FRACTION);
    }

    public function callAction(string $controllerName, string $methodName, array $urlParams, array $dtoJson = null) {

        // todo: здесь при деплое можно, значительно все ускорить, отказавшись от рефлексий
        // сгенерировать карту ctl => [method, paramType], и сразу из ассоциативки вытаскивать имя метода и тип
        // входного параметра

        // todo: надо сделать так что бы система могла работать как с закешированным мапингом,
        // так и полностью в динамическом режиме (для разработки)

        $fqcn = '\microapi\controller\\' . ucfirst($controllerName) . 'Controller';
        if (class_exists($fqcn)) {
            /** @var Controller $ctl */
            $ctl = new $fqcn;

            $methodName = 'action' . ucfirst($methodName);

            $ro     = new ReflectionObject($ctl);
            $method = $ro->getMethod($methodName);
            if ($method) {
                $params = $method->getParameters();
                if (count($params) === 0) {
                    return call_user_func([$ctl, $methodName]);
                }
                else {
                    $args = [];

                    for ($i = 0, $cnt = count($params); $i < $cnt; ++$i) {
                        $reflectionType = $params[$i]->getType();

                        if ($reflectionType->isBuiltin()) {
                            if (!isset($urlParams[$i])) {
                                throw new HttpException("incorrect number of args for {$controllerName}/{$methodName}");
                            }
                            $args[] = static::castType($urlParams[$i], (string)$reflectionType);
                        }
                        else {
                            // assume that last not-built in type is a DTO object

                            $class = $params[$i]->getClass();
                            /** @see \microapi\base\DTO */
                            if ($class->isSubclassOf('\microapi\base\DTO')) {
                                $paramType = $class->getName();

                                if (empty($dtoJson)) {
                                    $args[] = null;
                                }
                                else {
                                    /** @var DTO $dto */
                                    $dto    = new $paramType($dtoJson);

                                    if($dto->validate()){
                                        $args[] = $dto;
                                    }
                                    else{
                                        return ['error' => true, 'errors' => $dto->getErrors()];
                                    }
                                }
                            }
                        }
                    }

                    /** @var Controller $ctl */
                    if ($ctl->onBeforeAction($methodName)) {
                        return call_user_func_array([$ctl, $methodName], $args);
                    }

                    return [];
                }

            }
            throw new \microapi\http\HttpException('method not found', \microapi\http\HttpException::NOT_FOUND);

        }
        throw new \microapi\http\HttpException('controller not found', \microapi\http\HttpException::NOT_FOUND);

    }

    /**
     * @return string
     */
    private function getRawPost():string {
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

    public function extractUrlParams() : array {
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
            static::$instance = new App();
        }

        return static::$instance;
    }

    /**
     * HTTP method
     *
     * @return string
     */
    public function getMethod(): string {
        return $this->method;
    }

    /**
     * Repository of application components
     *
     * @return \microapi\base\Repository
     */
    public function getRepository() : Repository {
        if ($this->repository === null) {
            $this->repository = new Repository();
        }

        return $this->repository;
    }

    /**
     * add user component (object or callable) or data
     *
     * @param string $name
     * @param        $mixed
     * @return $this
     */
    public function addComp(string $name, $mixed) {
        $this->getRepository()->add($name, $mixed);

        return $this;
    }

    public function getComp(string $name) {
        return $this->getRepository()->get($name);
    }

    public function log() : \Psr\Log\LoggerInterface {
        try {
            return $this->getComp('log');
        }
        catch (RepositoryException $e) {
            return new \Psr\Log\NullLogger();
        }
    }

    protected function preDispatch() {
        $this->uri    = $_SERVER['REQUEST_URI'];
        $this->method = strtolower($_SERVER['REQUEST_METHOD']);

        $parsedUrl = parse_url($this->uri);
        $path      = ltrim($parsedUrl['path'], '//');
        // пропускаем первые несоклько сомпонентов пути типа /api
        $this->uriComponents = array_slice(explode('/', $path), static::SKIP_PATH_COMPONENTS);
    }
}

