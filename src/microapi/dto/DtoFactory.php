<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 18:38
 */

namespace microapi\dto;

interface DtoFactory {
    public function create (string $class, string $rawData) : DTO;
}