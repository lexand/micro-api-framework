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
     * all controllers should extends \microapi\base\Controller
     *
     * @param string $nsPrefix
     * @param array  $path
     * @return $this
     */
    public function addModulesNamespace(string $nsPrefix, array $path) {
        $this->modulesNamespaces[$nsPrefix] = $path;

        return $this;
    }

    public function build() {

    }
}