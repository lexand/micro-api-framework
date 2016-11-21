<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 22.09.16
 * Time: 16:18
 */

declare(strict_types = 1);

namespace microapi\base;

use microapi\App;
use microapi\http\HttpException;

class Controller {

    public function accessRules() { return []; }

    /**
     * @param string $action
     * @return bool
     * @throws \microapi\http\HttpException
     */
    public function onBeforeAction(string $action) : bool { return $this->actionFilter($action); }

    private function filterCheckAccess() : bool {

        if (!$this->getWebUser()->isLoggedIn()) {

            throw new HttpException('access denied', 401);
        }

        return true;
    }

    public function validateInputData(Validator $validator, DTO $object) {
        if (!$validator->validate($object)) {
            throw new HttpException('Validation failed');
        }
    }

    public function getWebUser() : WebUser {
        return App::get()->getComp('user');
    }

    private function actionFilter(string $action) : bool {
        $filters = $this->getActionFilters($action);

        if ($filters === []) {
            return true;
        }

        $res = true;
        foreach ($filters as $filter) {
            switch ($filter) {
                case 'checkAccess':
                    $res = $res && $this->filterCheckAccess();
                    break;
                default:
                    throw new \LogicException("filter {$filter} not found for action {$action}");
            }

            // ускоряем выход из цикла
            if(!$res){
                return false;
            }
        }

        return true;
    }

    private function getActionFilters(string $action) : array {
        $tmp = [];
        foreach ($this->accessRules() as $actions => $filters) {
            $acts = array_map('trim', explode(',', $actions));
            if (in_array($action, $acts, true)) {
                $tmp = array_unique($tmp, array_map('trim', explode(',', $filters)));
            }
        }
        return $tmp;
    }
}
