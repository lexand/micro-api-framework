<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 17:54
 */

declare(strict_types=1);

namespace microapi\endpoint;

/**
 * Class CacheBuilder
 *
 * Build cache for endpoints, which avoid using reflection on every request.
 *
 * It creates cache files for every found HTTP method. For example endpoints_get.php for all controllers actions which
 * should process GET request.
 *
 * @package microapi\endpoint
 */
class CacheBuilder {

    /**
     * @var string
     */
    private $padding = '  ';

    /**
     * @var string
     */
    private $cachePath;

    private $modulesNamespaces = [];

    /**
     * @param string $cachePath Path where cache filed will be stored.
     * @return \microapi\endpoint\CacheBuilder
     */
    public function setCachePath(string $cachePath): CacheBuilder {
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * all controllers should extends \microapi\Controller
     *
     * @param string $nsPrefix without leading and trailing slashes
     * @param array  $path
     * @return $this
     */
    public function addModulesNamespace(string $nsPrefix, array $path) {
        $this->modulesNamespaces[$nsPrefix] = $path;

        return $this;
    }

    /**
     * Base method for build endpoints cache
     */
    public function build() { $this->saveCache($this->extractData()); }

    private function addToCache(array &$cache, \ReflectionMethod $mr) {
        $ctlFqcn            = $mr->class;
        $actionMethodName   = $mr->getName();
        $meta['methodName'] = $actionMethodName;
        $meta['paramsMeta'] = Reflection::getParamsMeta($mr, false);
        $actionMethodName   = strtolower(substr($actionMethodName, 6));

        foreach (Reflection::getActionHttpMethods($mr) as $httpMethod) {
            if (!isset($cache[$httpMethod])) {
                $cache[$httpMethod] = [];
            }

            if (!isset($cache[$httpMethod][$ctlFqcn])) {
                $cache[$httpMethod][$ctlFqcn] = [];
            }

            if (!isset($cache[$httpMethod][$ctlFqcn][$actionMethodName])) {
                $cache[$httpMethod][$ctlFqcn][$actionMethodName] = $meta;
            }
        }
    }

    private function saveCache(array $rawCache) {
        $pl1 = str_repeat($this->padding, 1);
        $pl2 = str_repeat($this->padding, 2);
        $pl3 = str_repeat($this->padding, 3);
        foreach ($rawCache as $httpMethod => $controllersData) {
            $fh = fopen($this->cachePath . '/endpoints_' . $httpMethod . '.php', 'wb');

            $this->writeHeader($fh);
            fwrite($fh, "return [\n");

            foreach ($controllersData as $ctlFqcn => $actionsData) {
                fwrite($fh, sprintf("%s'%s' => [\n", $pl1, $ctlFqcn));
                foreach ($actionsData as $action => $actionMeta) {
                    fwrite($fh, sprintf("%s'%s' => [\n", $pl2, $action));
                    fwrite($fh, sprintf("%s'methodName' => %s,\n", $pl3, "'{$actionMeta['methodName']}'"));
                    fwrite($fh, sprintf("%s'paramsMeta' => [\n", $pl3));
                    $this->writeParamsMeta($fh, $actionMeta['paramsMeta']);
                    fwrite($fh, sprintf("%s],\n", $pl3));
                    fwrite($fh, sprintf("%s],\n", $pl2));
                }
                fwrite($fh, sprintf("%s],\n", $pl1));
            }

            fwrite($fh, "];\n");
            fclose($fh);
        }
    }

    private function writeHeader($fh) {
        //language=php
        $hdr = <<< __HDR__
<?php
/**
 * This is auto generated file
 * 
 * Please do not chane it if you are not sure/
 */ 


__HDR__;
        fwrite($fh, $hdr);
    }

    private function writeParamsMeta($fh, array $paramsMeta) {
        $pl3 = str_repeat($this->padding, 4);
        $pl4 = str_repeat($this->padding, 5);
        foreach ($paramsMeta as $paramName => $paramMeta) {
            fwrite($fh, sprintf("%s'%s' => [\n", $pl3, $paramName));

            $isConst = false;
            if (isset($paramMeta['defaultIsConstant'])) {
                $isConst = true;
                unset($paramMeta['defaultIsConstant']);
            }

            foreach ($paramMeta as $key => $value) {
                if ($isConst && ($key === 'default')) {
                    fwrite($fh, sprintf("%s'%s' => %s,\n", $pl4, $key, $value));
                    continue;
                }
                fwrite($fh, sprintf("%s'%s' => %s,\n", $pl4, $key, static::wrapBuiltInType($value)));
            }

            fwrite($fh, sprintf("%s],\n", $pl3));
        }
    }

    public static function wrapBuiltInType($value): string {
        switch (true) {
            case is_int($value):
                return (string)$value;
            case is_float($value):
                return (string)$value;
            case is_bool($value):
                return $value ? 'true' : 'false';
            case is_string($value):
                return '\'' . $value . '\'';
            case is_null($value):
                return 'null';
            case is_array($value):
                return '[]';
            default:
                throw new \LogicException('unsupported built-in type');
        }
    }

    /**
     * @return array
     * @internal
     */
    public function extractData(): array {
        $rawCache = [];

        foreach ($this->modulesNamespaces as $nsPrefix => $paths) {
            foreach ($paths as $path) {
                $pathLen  = strlen($path);
                $realPath = $path . '/' . str_replace('\\', '/', $nsPrefix);
                $di       = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($realPath));
                /** @var \SplFileInfo $fi */
                foreach ($di as $fi) {
                    if ($fi->isDir()) {
                        continue;
                    }
                    $pathName = $fi->getPathname();

                    if (substr($pathName, strlen($pathName) - 7) === 'Ctl.php') {

                        $fqcn = str_replace('/', '\\', substr($pathName, $pathLen, -4));

                        $ctlReflection = new \ReflectionClass($fqcn);
                        $methods       = $ctlReflection->getMethods(\ReflectionMethod::IS_PUBLIC);
                        foreach ($methods as $method) {
                            if (substr($method->getName(), 0, 6) === 'action') {
                                $this->addToCache($rawCache, $method);
                            }
                        }
                    }
                }
            }
        }

        return $rawCache;
    }
}
