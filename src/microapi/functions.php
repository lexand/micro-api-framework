<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 27.07.17
 * Time: 21:25
 */

namespace microapi;

use Psr\Http\Message\ResponseInterface;
use function GuzzleHttp\Psr7\copy_to_string;

function send_response(ResponseInterface $r) {
    header('HTTP/' . $r->getProtocolVersion() . ' ' . $r->getStatusCode() . ' ' . $r->getReasonPhrase());

    foreach ($r->getHeaders() as $name => $values) {
        foreach ($values as $value) {
            header(sprintf('%s: %s', $name, $value), false);
        }
    }

    echo copy_to_string($r->getBody());
}
