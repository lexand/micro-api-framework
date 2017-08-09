<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 19:20
 */

declare(strict_types=1);

namespace microapi\event\object;

use microapi\Controller;
use microapi\event\Event;

class BeforeAction extends Event {
    /**
     * @var string
     */
    public $action;
    /**
     * @var Controller
     */
    public $controller;
    /**
     * @var array
     */
    public $params;

    /**
     *  constructor.
     *
     * @param Controller $controller
     * @param string     $action
     * @param array      $params
     */
    public function __construct(Controller $controller, string $action, array $params = []) {
        $this->action     = $action;
        $this->controller = $controller;
        $this->params     = $params;
    }
}