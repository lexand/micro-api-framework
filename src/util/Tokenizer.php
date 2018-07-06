<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 28.07.17
 * Time: 11:11
 */

declare(strict_types=1);

namespace microapi\util;

class Tokenizer {
    private $data = [];

    /**
     * Tokenizer constructor.
     *
     * @param string $src
     * @param string $delimiter
     * @param int    $skip
     */
    public function __construct(string $src, string $delimiter, int $skip) {
        $src = \trim($src, $delimiter);
        if ($src === '') {
            $this->data = [];
        }
        else {
            $data       = \array_map('trim', \explode($delimiter, $src));
            $data       = \array_slice($data, $skip);
            $this->data = $data;
        }
    }

    /**
     * @return string|null
     */
    public function next() : ?string {
        return array_shift($this->data);
    }
}
