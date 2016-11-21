<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 15:44
 */

declare(strict_types = 1);

namespace microapi\base;

use microapi\db\DBStmt;
use microapi\model\User;
use microapi\secure\Password;

class WebUser {

    /**
     * WebUser constructor.
     */
    public function __construct() { $this->init(); }

    public function init() {
        if (PHP_SAPI !== 'cli') {
            session_start();
        }
    }

    public function isLoggedIn() { return isset($_SESSION['userId']); }

    public function getId() {
        if ($this->isLoggedIn()) {
            return $_SESSION['userId'];
        }

        return null;
    }

    public function login(string $login, string $password) : bool {
        $user = User::m()->getByLogin($login);

        if ($user && Password::comparePassword($password, $user->getPwd())) {
            $_SESSION['userId'] = $user->getId();
            $_SESSION['userLogin'] = $login;
            $user->update(['lastLogin' => new DBStmt('NOW()')]);

            return true;
        }

        return false;
    }

    public function logout() {
        if (isset($_SESSION['userId'])) {
            unset($_SESSION['userId']);
            unset($_SESSION['userLogin']);
            session_destroy();
        }
    }
}