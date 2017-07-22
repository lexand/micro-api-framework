<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 19.09.16
 * Time: 12:49
 */

declare(strict_types=1);

namespace microapi\http;

class HttpException extends \RuntimeException {

    const NOT_FOUND    = 404;
    const FORBIDDEN    = 403;
    const SERVER_ERROR = 500;
}
