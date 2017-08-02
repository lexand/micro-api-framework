<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 02.08.17
 * Time: 15:40
 */

declare(strict_types=1);

namespace microapi\http;

use Psr\Http\Message\ResponseInterface;

interface ResponseFactory {
    public function create(): ResponseInterface;
}