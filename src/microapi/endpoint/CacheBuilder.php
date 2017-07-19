<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 17.07.17
 * Time: 17:54
 */

declare(strict_types=1);

namespace microapi\endpoint;

class CacheBuilder {

    /**
     * @var string
     */
    private $cachePath;

    private $modulesNamespaces = [];

    public function setCachePath(string $cachePath): CacheBuilder {
        $this->cachePath = $cachePath;

        return $this;
    }

    /**
     * all controllers should extends \microapiController
     *
     * @param string $nsPrefix without leading and trailing slashes
     * @param array  $path
     * @return $this
     */
    public function addModulesNamespace(string $nsPrefix, array $path) {
        $this->modulesNamespaces[$nsPrefix] = $path;

        return $this;
    }

    public function build() {

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
                            if (strpos($method->getName(), 'action') === 0) {
                                $this->addToCache(
                                    $rawCache,
                                    $fqcn,
                                    $method->getName(),
                                    Reflection::getParamsMeta($method, false),
                                    Reflection::getActionHttpMethods($method)
                                );
                                print_r(Reflection::getParamsMeta($method, false));
                            }
                        }
                    }
                }
            }
        }

        $this->saveCache($rawCache);
    }

    private function addToCache(array &$cache,
                                string $ctlFqcn,
                                string $actionName,
                                array $paramsMeta,
                                array $httpMethods) {

        // todo: this
    }

    private function saveCache(array $rawCache) { }

}