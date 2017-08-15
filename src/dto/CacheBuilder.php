<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 09.08.17
 * Time: 19:15
 */
declare(strict_types=1);

namespace microapi\dto;

class CacheBuilder {

    use DtoTypeAnnotationTrait;

    /**
     * @var string
     */
    private $path;

    private $namespaces = [];

    public function __construct(string $path) { $this->path = $path; }

    public static function create(string $path): CacheBuilder { return new self($path); }

    public function addNamespace(string $ns, array $paths): CacheBuilder {
        $this->namespaces[$ns] = $paths;

        return $this;
    }

    public function build() {
        foreach ($this->namespaces as $nsPrefix => $paths) {
            foreach ($paths as $path) {
                $pathLen  = strlen($path);
                $realPath = $path . '/' . str_replace('\\', '/', $nsPrefix) . '/controller';
                $di       = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($realPath));

                foreach ($di as $fi) {
                    if ($fi->isDir()) {
                        continue;
                    }
                    $pathName = $fi->getPathname();

                    if (substr($pathName, strlen($pathName) - 7) === 'Ctl.php') {

                        $fqcn = str_replace('/', '\\', substr($pathName, $pathLen, -4));

                        $r = new \ReflectionClass($fqcn);
                        if ($r->isInstantiable()) {

                            $propsMeta = [];

                            $props = $r->getProperties(\ReflectionProperty::IS_PUBLIC);
                            foreach ($props as $prop) {
                                $name = $prop->getName();
                                $meta = self::annotatedMeta($prop->getDocComment());

                                if (!$meta['exposed']) {
                                    continue;
                                }

                                $propsMeta[$name] = $meta;
                            }
                            $this->saveCache($fqcn, $propsMeta);
                        }
                    }
                }

            }
        }
    }

    private function saveCache(string $fqcn, array $propsMeta) {
        $file = $this->path . '/';
        $file .= str_replace('\\', '_', $fqcn) . '.php';

        $tmpFile = $this->path . '/' . uniqid('dtocache', true);

        $export = var_export($propsMeta, true);
        $code   = <<< __PHP__
<?php        
/**
 ************************************
 *    THIS IS AUTO GENERATED FILE
 *  DONT TOUCH THIS IF YOU NOT SURE
 ************************************
 */
 
return {$export};
 
__PHP__;


        file_put_contents($tmpFile, $code);
        rename($tmpFile, $file);
    }
}
