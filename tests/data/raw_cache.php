<?php
/**
 * Created by IntelliJ IDEA.
 * User: alex
 * Date: 22.07.17
 * Time: 15:08
 */

return [
    'get'  =>
        [
            'app\\controller\\Test6547586Ctl' =>
                [
                    'get'              =>
                        [
                            'methodName' => 'actionGet',
                            'paramsMeta' =>
                                [
                                ],
                        ],
                    'getparametrized'  =>
                        [
                            'methodName' => 'actionGetparametrized',
                            'paramsMeta' =>
                                [
                                    'name' =>
                                        [
                                            'optional' => false,
                                            'type'     => 'string',
                                            'builtin'  => true,
                                        ],
                                    'age'  =>
                                        [
                                            'optional' => false,
                                            'type'     => 'int',
                                            'builtin'  => true,
                                        ],
                                ],
                        ],
                    'postparametrized' =>
                        [
                            'methodName' => 'actionPostparametrized',
                            'paramsMeta' =>
                                [
                                    'a' =>
                                        [
                                            'optional' => false,
                                            'type'     => 'int',
                                            'builtin'  => true,
                                        ],
                                    'b' =>
                                        [
                                            'optional' => false,
                                            'type'     => 'float',
                                            'builtin'  => true,
                                        ],
                                    'c' =>
                                        [
                                            'optional' => false,
                                            'type'     => 'string',
                                            'builtin'  => true,
                                        ],
                                    'd' =>
                                        [
                                            'optional' => false,
                                            'type'     => 'bool',
                                            'builtin'  => true,
                                        ],
                                ],
                        ],
                    'postdto'          =>
                        [
                            'methodName' => 'actionPostdto',
                            'paramsMeta' =>
                                [
                                    'dto' =>
                                        [
                                            'optional' => false,
                                            'builtin'  => false,
                                            'type'     => 'app\\dto\\TestDTO',
                                        ],
                                ],
                        ],
                    'checkconst'       =>
                        [
                            'methodName' => 'actionCheckconst',
                            'paramsMeta' =>
                                [
                                    'c' =>
                                        [
                                            'optional'          => true,
                                            'type'              => 'string',
                                            'builtin'           => true,
                                            'defaultIsConstant' => true,
                                            'default'           => 'app\\controller\\Test6547586Ctl::SOME_CONSTANT',
                                        ],
                                ],
                        ],
                ],
        ],
    'post' =>
        [
            'app\\controller\\Test6547586Ctl' =>
                [
                    'postdto'       =>
                        [
                            'methodName' => 'actionPostdto',
                            'paramsMeta' =>
                                [
                                    'dto' =>
                                        [
                                            'optional' => false,
                                            'builtin'  => false,
                                            'type'     => 'app\\dto\\TestDTO',
                                        ],
                                ],
                        ],
                    'postmixedargs' =>
                        [
                            'methodName' => 'actionPostmixedargs',
                            'paramsMeta' =>
                                [
                                    'c'   =>
                                        [
                                            'optional' => false,
                                            'type'     => 'string',
                                            'builtin'  => true,
                                        ],
                                    'dto' =>
                                        [
                                            'optional' => false,
                                            'builtin'  => false,
                                            'type'     => 'app\\dto\\TestDTO',
                                        ],
                                ],
                        ],
                    'checkconst'    =>
                        [
                            'methodName' => 'actionCheckconst',
                            'paramsMeta' =>
                                [
                                    'c' =>
                                        [
                                            'optional'          => true,
                                            'type'              => 'string',
                                            'builtin'           => true,
                                            'defaultIsConstant' => true,
                                            'default'           => 'app\\controller\\Test6547586Ctl::SOME_CONSTANT',
                                        ],
                                ],
                        ],
                ],
        ],
];