<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 22.09.16
 * Time: 16:18
 */

declare(strict_types=1);

namespace microapi\base;

use microapi\base\events\EventDriven;
use microapi\base\events\EventObject;
use microapi\base\events\Events;
use microapi\http\HttpException;

class Controller implements EventDriven {

    use Events;

    /**
     * @param string $action
     * @param array  $params
     * @return bool
     */
    public function beforeAction(string $action, array $params = []): bool {
        return $this->trigger(
            'beforeaction',
            new class($this, $action, $params) extends EventObject {
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
        )->isSuccess();
    }

    public function validateInputData(Validator $validator, DTO $object) {
        if (!$validator->validate($object)) {
            throw new HttpException('Validation failed');
        }
    }
}
