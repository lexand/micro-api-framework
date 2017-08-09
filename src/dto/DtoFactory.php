<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:38
 */

namespace microapi\dto;

use Psr\Http\Message\StreamInterface;

interface DtoFactory {
    public function createFromStream (string $class, StreamInterface $stream) : DTO;
}