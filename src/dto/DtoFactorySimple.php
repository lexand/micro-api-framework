<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 10.08.17
 * Time: 14:46
 */
declare(strict_types=1);

namespace microapi\dto;

use Psr\Http\Message\StreamInterface;

/**
 * Class DtoFactorySimple
 *
 * - all public fields of DTO object may be filled by from input data, as this factory does not have any guard
 * mechanism for that.
 * - this factory does not support nested or objects (DTO or general)
 * - supports only array of scalars or arrays of associative arrays
 *
 * @package microapi\dto
 * @see     \microapi\dto\DTO
 */
class DtoFactorySimple implements DtoFactory {
    public function createFromStream(string $class, StreamInterface $stream): DTO {
        $fields = json_decode($stream->getContents(), true);

        return $this->createFromData($class, $fields);
    }

    public function createFromData(string $class, array $data): DTO {
        $obj    = new $class;
        $fields = get_object_vars($obj);
        foreach ($fields as $name) {
            if (array_key_exists($name, $data)) {
                $obj->{$name} = $data[$name];
            }
        }

        return $obj;
    }
}