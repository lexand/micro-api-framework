<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex1
 * Date: 30.09.16
 * Time: 13:57
 */

namespace admin\controller;

use microapi\Controller;

class Test6547586Ctl extends Controller {
    const SOME_CONSTANT = '12';

    /**
     * @return array
     * @methods(get)
     */
    public function actionIndex() {
        return ['prompt' => 'hello this is index action from admin module'];
    }
}
