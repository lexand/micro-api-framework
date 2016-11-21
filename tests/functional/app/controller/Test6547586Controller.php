<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 30.09.16
 * Time: 13:57
 */

namespace microapi\controller;

use microapi\base\Controller;
use microapi\dto\TestDTO;

class Test6547586Controller extends Controller {
    public function actionGet() {
        return ['simple_response' => 1];
    }

    public function actionGetparametrized(string $name, int $age) {
        return ['name' => $name, 'age' => $age];
    }

    public function actionPostparametrized(int $a, float $b, string $c, bool $d) {
        return compact(['a', 'b', 'c', 'd']);
    }

    public function actionPostdto(TestDTO $dto) : TestDTO{
        return $dto;
    }

    public function actionPostmixedargs(string $c, TestDTO $dto) {
        return compact('c', 'dto');
    }

}
