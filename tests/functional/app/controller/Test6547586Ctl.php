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

    /**
     * @return array
     * @methods (get)
     */
    public function actionGet() {
        return ['simple_response' => 1];
    }

    /**
     * @param string $name
     * @param int    $age
     * @return array
     * @methods (get)
     */
    public function actionGetparametrized(string $name, int $age) {
        return ['name' => $name, 'age' => $age];
    }

    /**
     * @param int    $a
     * @param float  $b
     * @param string $c
     * @param bool   $d
     * @return array
     * @methods (get,post)
     */
    public function actionPostparametrized(int $a, float $b, string $c, bool $d) {
        return compact(['a', 'b', 'c', 'd']);
    }

    /**
     * @param \app\dto\TestDTO $dto
     * @return \app\dto\TestDTO
     * @methods (get, post)
     */
    public function actionPostdto(TestDTO $dto) : TestDTO{
        return $dto;
    }

    /**
     * @param string           $c
     * @param \app\dto\TestDTO $dto
     * @return array
     * @methods (post)
     */
    public function actionPostmixedargs(string $c, TestDTO $dto) {
        return compact('c', 'dto');
    }

    /**
     * @param string $c
     * @return array
     * @methods (get, post)
     */
    public function actionCheckconst(string $c = Test6547586Ctl::SOME_CONSTANT ) {
        return [];
    }


}
