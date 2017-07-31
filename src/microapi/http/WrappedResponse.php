<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 31.07.17
 * Time: 20:48
 */

namespace microapi\http;

use GuzzleHttp\Psr7\Response;

class WrappedResponse {
    /**
     * @var array|\Throwable|\microapi\dto\DTO
     */
    public $data;

    /**
     * @var \Psr\Http\Message\ServerRequestInterface
     */
    public $request;

    /**
     * @var \Psr\Http\Message\ResponseInterface
     */
    public $response;

    /**
     * DataResponse constructor.
     *
     * @param array|\microapi\dto\DTO|\Throwable       $data
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface      $response
     */
    public function __construct(
        $data,
        \Psr\Http\Message\ServerRequestInterface $request,
        \Psr\Http\Message\ResponseInterface $response = null
    ) {
        $this->data     = $data;
        $this->request  = $request;
        $this->response = $response ?? new Response();
    }
}
