<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 19:28
 */

declare(strict_types=1);

namespace microapi\endpoint;

use microapi\endpoint\exceptions\EndpointActionNotFoundException;
use microapi\endpoint\exceptions\EndpointControllerNotFoundException;
use Psr\Http\Message\ServerRequestInterface;

class Reflection {
    /**
     * @var ServerRequestInterface
     */
    private $request;
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
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param string                                   $fqcnCtl
     * @param string                                   $action
     */
    public function __construct(ServerRequestInterface $request, string $fqcnCtl, string $action) {
        $this->request = $request;
        $this->fqcnCtl = $fqcnCtl;
        $this->action  = strtolower($action);
    }

    public function getEndpoint(): Endpoint {
        if (class_exists($this->fqcnCtl)) {
            $ctl     = new $this->fqcnCtl();
            $ctlRefl = new \ReflectionObject($ctl);
            $found   = false;
            /** @var \ReflectionMethod $mr */
            foreach ($ctlRefl->getMethods(\ReflectionMethod::IS_PUBLIC) as $mr) {
                if ($this->action === strtolower(substr($mr->getName(), 6))) {
                    $found = true;
                    break;
                }
            }

            if ($found && static::isHttpMethodAllowed($this->request->getMethod(), $mr)) {
                return new Endpoint(
                    $this->request,
                    $ctl,
                    [
                        'methodName' => $mr->getName(),
                        'paramsMeta' => static::getParamsMeta($mr, true)
                    ]
                );
            }

            throw new EndpointActionNotFoundException("'{$this->action}' not found in '{$this->fqcnCtl}'");
        }
        throw new EndpointControllerNotFoundException("'{$this->fqcnCtl}' not found");
    }

    public static function isHttpMethodAllowed(string $method, \ReflectionMethod $mr): bool {
        return in_array(strtolower($method), static::getActionHttpMethods($mr), true);
    }

    public static function getActionHttpMethods(\ReflectionMethod $mr): array {
        $doc = $mr->getDocComment();

        if ($doc !== false) {
            $matches = [];
            preg_match('/@methods\s*\((.+?)\)/m', $doc, $matches);

            if (count($matches) === 2) {
                return array_map(
                    function (string $el): string {
                        return strtolower(trim($el));
                    },
                    explode(',', $matches[1])
                );
            }
        }

        return [];
    }

    /**
     * @param \ReflectionMethod $mr
     * @param bool              $getConstVal get constant value instead of constant name
     * @return array
     */
    public static function getParamsMeta(\ReflectionMethod $mr, bool $getConstVal = false): array {
        $params = [];
        /** @var \ReflectionParameter $pr */
        foreach ($mr->getParameters() as $pr) {
            $name    = $pr->getName();
            $type    = $pr->getType();
            $argData = [];

            $argData['optional'] = $pr->isOptional();
            if ($type->isBuiltin()) {
                $argData['type']    = (string)$type;
                $argData['builtin'] = true;
                if ($argData['optional']) {
                    if ($pr->isDefaultValueConstant()) {
                        $argData['defaultIsConstant'] = !$getConstVal;
                        $argData['default']           = $getConstVal
                            ? $pr->getDefaultValue()
                            : $pr->getDefaultValueConstantName();
                    }
                    else {
                        $argData['defaultIsConstant'] = false;
                        $argData['default']           = $pr->getDefaultValue();
                    }
                }
            }
            else {
                $argData['builtin'] = false;
                $argData['type']    = $pr->getClass()->getName();
                if ($argData['optional']) {
                    $argData['default'] = null;
                }
            }

            $params[$name] = $argData;
        }

        return $params;
    }

}