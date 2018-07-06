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

    /**
     *
     * - namaspaces should not intersect
     * - if you have classes in \one\two namespace, and in \one\two\three namespace? add only top level NS : \one\two
     *
     * @param string $nsPrefix
     * @param array  $paths
     *
     * @return \microapi\dto\CacheBuilder
     */
    public function addNamespace(string $nsPrefix, array $paths): CacheBuilder {
        $nsPrefix                    = trim($nsPrefix, '\\');
        $this->namespaces[$nsPrefix] = $paths;

        return $this;
    }

    public function build(): void {

        $processed = [];

        foreach ($this->namespaces as $nsPrefix => $paths) {
            foreach ($paths as $path) {
                $pathLen = \strlen($path) + 1;
                $di      = new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator(
                        $path,
                        \RecursiveDirectoryIterator::SKIP_DOTS
                    )
                );

                /** @var \SplFileInfo $fi */
                foreach ($di as $fi) {
                    if ($fi->isDir()) {
                        continue;
                    }
                    $pathName = $fi->getPathname();

                    if ($fi->getExtension() === 'php') {

                        $fqcn = $nsPrefix . '\\' . \str_replace('/', '\\', \substr($pathName, $pathLen, -4));

                        try {
                            $r = new \ReflectionClass($fqcn);
                            // info : filter by name space \ReflectionClass::getNamespaceName VS $nsPrefix
                            if (
                                !isset($processed[$fqcn])
                                && $r->isInstantiable()
                                && $r->isSubclassOf(DTO::class)
                            ) {

                                $processed[$fqcn] = 1;

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
                        catch (\ReflectionException $ignored) {

                        }
                    }
                }

            }
        }
    }

    private function saveCache(string $fqcn, array $propsMeta): void {
        $file = $this->path . '/';
        $file .= \str_replace('\\', '_', $fqcn) . '.php';

        $tmpFile = $this->path . '/' . \uniqid('dtocache', true);

        $export = \var_export($propsMeta, true);
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


        \file_put_contents($tmpFile, $code);
        \rename($tmpFile, $file);
    }
}
