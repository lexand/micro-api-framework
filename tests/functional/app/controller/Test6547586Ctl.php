<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 30.09.16
 * Time: 13:57
 */

namespace app\controller;

use microapi\Controller;
use app\dto\TestDTO;

class Test6547586Ctl extends Controller {
    const SOME_CONSTANT = '12';

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

    public function actionCheckconst(string $c = Test6547586Ctl::SOME_CONSTANT ) {
        return [];
    }


}
