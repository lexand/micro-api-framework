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
                    'getpostdto'          =>
                        [
                            'methodName' => 'actionGetPostdto',
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
                    'index'              =>
                        [
                            'methodName' => 'actionIndex',
                            'paramsMeta' =>
                                [
                                ],
                        ],

                ],
            'app\\controller\\Main6547586Ctl' =>
                [
                    'index'              =>
                        [
                            'methodName' => 'actionIndex',
                            'paramsMeta' =>
                                [
                                ],
                        ],
                ]
        ],
    'post' =>
        [
            'app\\controller\\Test6547586Ctl' =>
                [
                    'getpostdto'       =>
                        [
                            'methodName' => 'actionGetPostdto',
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
                ],
        ],
];