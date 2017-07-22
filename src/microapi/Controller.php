<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 22.09.16
 * Time: 16:18
 */

declare(strict_types=1);

namespace microapi;

use microapi\dto\DTO;
use microapi\dto\Validator;
use microapi\event\EventDriven;
use microapi\event\Events;
use microapi\event\object\AfterAction;
use microapi\event\object\BeforeAction;
use microapi\http\HttpException;

class Controller implements EventDriven {

    use Events;

    /**
     * @param string $action
     * @param array  $params
     * @return bool
     */
    public function beforeAction(string $action, array $params = []): bool {
        return !$this->trigger('beforeaction', new BeforeAction($this, $action, $params))->isStopped();
    }

    public function validateInputData(Validator $validator, DTO $object) {
        if (!$validator->validate($object)) {
            throw new HttpException('Validation failed');
        }
    }

    public function afterAction(string $action, $res) {
        $responseEvent = new AfterAction($this, $action, $res);

        $this->trigger('afteraction', $responseEvent);

        return $responseEvent->response;
    }
}
