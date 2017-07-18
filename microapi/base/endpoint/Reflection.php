<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:28
 */

declare(strict_types=1);

namespace microapi\endpoint;

use microapi\base\endpoint\EndpointActionNotFoundException;
use microapi\base\endpoint\EndpointControllerNotFoundException;
use microapi\base\endpoint\Endpoint;

class Reflection {
    /**
     * @var string
     */
    private $method;
    /**
     * @var string
     */
    private $fqcnCtl;
    /**
     * @var string
     */
    private $action;


    /**
     * Reflection constructor.
     *
     * @param string $method
     * @param string $fqcnCtl
     * @param string $action
     */
    public function __construct(string $method, string $fqcnCtl, string $action) {
        $this->method  = $method;
        $this->fqcnCtl = $fqcnCtl;
        $this->action  = strtolower($action);
    }

    public function create(array $paramsData): Endpoint {
        // todo : this
    }

    public function getEndpoint(): Endpoint {
        if (class_exists($this->fqcnCtl)) {
            $ctl        = new $this->fqcnCtl();
            $ctlRefl    = new \ReflectionObject($ctl);
            $methodName = 'action' . $this->action;
            $found      = false;
            /** @var \ReflectionMethod $mr */
            foreach ($ctlRefl->getMethods() as $mr) {
                if ($methodName === strtolower($mr->getName())) {
                    $found = true;
                    break;
                }
            }

            if ($found) {
                $methodName = $mr->getName();

                if (static::isMethodAllowed($this->method, $mr->getDocComment())) {
                    return new Endpoint(
                        $this->method,
                        $ctl,
                        $methodName,
                        static::getParamsData($mr)
                    );
                }
            }

            throw new EndpointActionNotFoundException("'{$this->action}' not found in '{$this->fqcnCtl}'");
        }
        throw new EndpointControllerNotFoundException("'{$this->fqcnCtl}' not found");
    }

    public static function isMethodAllowed(string $method, string $comment): bool {
        // todo: this
        return true;
    }

    /**
     * @param \ReflectionMethod $mr
     * @return array
     */
    public static function getParamsData(\ReflectionMethod $mr): array {
        $params = [];
        /** @var \ReflectionParameter $pr */
        foreach ($mr->getParameters() as $pr) {
            $name    = $pr->getName();
            $type    = $pr->getType();
            $argData = [];

            $argData['optional'] = $pr->isOptional();
            if ($type->isBuiltin()) {
                $argData['type']     = (string)$type;
                $argData['builtin'] = true;
                if ($argData['optional']) {
                    if ($pr->isDefaultValueConstant()) {
                        $argData['defaultIsConstant'] = true;
                        $argData['default']           = $pr->getDefaultValueConstantName();
                    }
                    else {
                        $argData['defaultIsConstant'] = false;
                        $argData['default']           = $pr->getDefaultValue();
                    }
                }
            }
            else{
                $argData['builtin'] = false;
                $argData['type'] = $pr->getClass()->getName();
                if ($argData['optional']) {
                    $argData['default'] = null;
                }
            }

            $params[$name] = $argData;
        }

        return $params;
    }

}