<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:39
 */

declare(strict_types=1);

namespace microapi\dto;

use Psr\Http\Message\StreamInterface;

class DtoFactoryDefault implements DtoFactory {

    public function create(string $class, StreamInterface $stream): DTO {
        return new $class(json_decode($stream->getContents(), true));
    }
}
