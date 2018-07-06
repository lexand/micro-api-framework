<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 02.08.17
 * Time: 15:38
 */

namespace microapi\http;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\ResponseInterface;

class DefaultResponseFactory implements ResponseFactory {
    /**
     * @var int
     */
    private $status;
    /**
     * @var array
     */
    private $headers;
    /**
     * @var null
     */
    private $body;
    /**
     * @var string
     */
    private $version;
    /**
     * @var null
     */
    private $reason;

    /**
     * DefaultResponseFactory constructor.
     *
     * @param int         $status
     * @param array       $headers
     * @param null        $body
     * @param string      $version
     * @param string|null $reason
     */
    public function __construct(
        int $status = 200,
        array $headers = [],
        $body = null,
        string $version = '1.1',
        string $reason = null
    ) {

        $this->status  = $status;
        $this->headers = $headers;
        $this->body    = $body;
        $this->version = $version;
        $this->reason  = $reason;
    }

    public function create(): ResponseInterface {
        return new Response(
            $this->status,
            $this->headers,
            $this->body,
            $this->version,
            $this->reason
        );
    }
}
