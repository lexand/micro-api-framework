<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 22.09.16
 * Time: 16:18
 */

declare(strict_types = 1);

namespace microapi\base;

use microapi\base\events\EventDriven;
use microapi\base\events\EventObject;
use microapi\base\events\Events;
use microapi\http\HttpException;

class Controller implements EventDriven {

    use Events;

    /**
     * @param string $action
     * @return bool
     * @throws \microapi\http\HttpException
     */
    public function beforeAction(string $action) : bool {
        return $this->trigger(
            'beforeaction',
            new class($action, $this) extends EventObject {
                /**
                 * @var string
                 */
                public $action;
                /**
                 * @var Controller
                 */
                public $controller;

                /**
                 *  constructor.
                 *
                 * @param string     $action
                 * @param Controller $controller
                 */
                public function __construct($action, Controller $controller) {
                    $this->action     = $action;
                    $this->controller = $controller;
                }
            }
            )->isSuccess();
    }

    public function validateInputData(Validator $validator, DTO $object) {
        if (!$validator->validate($object)) {
            throw new HttpException('Validation failed');
        }
    }
}
