<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 04.10.16
 * Time: 11:50
 */

declare(strict_types=1);

namespace microapi\secure;

class Password {

    const PASSWORD_HASH_COST = 14;

    public static function hashPassword(string $pwd) : string {
        return password_hash($pwd, PASSWORD_BCRYPT, ['cost' => self::PASSWORD_HASH_COST]);
    }

    public static function comparePassword(string $pwd, string $hash) : bool {
        return password_verify($pwd, $hash);
    }
}