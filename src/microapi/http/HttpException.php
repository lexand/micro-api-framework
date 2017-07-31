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

    const BAD_REQUEST         = 400;
    const NOT_FOUND           = 404;
    const FORBIDDEN           = 403;
    const PRECONDITION_FAILED = 412;
    const EXPECTATION_FAILED  = 417;
    const SERVER_ERROR        = 500;

    public function __construct($message = '', $code = HttpException::BAD_REQUEST, \Throwable $previous = null) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }


}
