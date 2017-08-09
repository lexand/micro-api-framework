<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 19:19
 */

declare(strict_types=1);

namespace microapi\event\object;

use microapi\Controller;
use microapi\event\Event;

class AfterAction extends Event {
    /**
     * @var string
     */
    public $action;
    /**
     * @var Controller
     */
    public $controller;
    /**
     * @var mixed
     */
    public $actionResult;

    /**
     *  constructor.
     *
     * @param Controller $controller
     * @param string     $action
     * @param mixed      $actionResult
     */
    public function __construct(Controller $controller, string $action, $actionResult) {
        $this->action       = $action;
        $this->controller   = $controller;
        $this->actionResult = $actionResult;
    }
}