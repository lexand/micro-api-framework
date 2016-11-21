<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 03.10.16
 * Time: 15:44
 */

declare(strict_types = 1);

namespace microapi\base;

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

    public function login(string $login, string $password) : bool {return false;}

    public function logout() {session_destroy();}
}