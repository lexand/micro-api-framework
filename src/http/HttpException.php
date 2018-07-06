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

    public const BAD_REQUEST         = 400;
    public const NOT_FOUND           = 404;
    public const FORBIDDEN           = 403;
    public const PRECONDITION_FAILED = 412;
    public const EXPECTATION_FAILED  = 417;
    public const SERVER_ERROR        = 500;

    public function __construct($message = '', $code = HttpException::BAD_REQUEST, \Throwable $previous = null) {
        parent::__construct(
            $message,
            $code,
            $previous
        );
    }


}
