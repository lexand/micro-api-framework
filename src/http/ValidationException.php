<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 31.07.17
 * Time: 21:30
 */

declare(strict_types=1);

namespace microapi\http;

class ValidationException extends HttpException {
    /**
     * @var array
     */
    private $errors;

    public function __construct(array $errors, $code = HttpException::EXPECTATION_FAILED, \Throwable $previous = null) {
        parent::__construct('', $code, $previous);
        $this->errors = $errors;
    }

    public function getErrors(): array { return $this->errors; }
}