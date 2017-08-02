<?php
/**
 * This is auto generated file
 * 
 * Please do not chane it if you are not sure/
 */ 

return [
  'app\controller\Test6547586Ctl' => [
    'postparametrized' => [
      'methodName' => 'actionPostparametrized',
      'paramsMeta' => [
        'a' => [
          'optional' => false,
          'type' => 'int',
          'builtin' => true,
        ],
        'b' => [
          'optional' => false,
          'type' => 'float',
          'builtin' => true,
        ],
        'c' => [
          'optional' => false,
          'type' => 'string',
          'builtin' => true,
        ],
        'd' => [
          'optional' => false,
          'type' => 'bool',
          'builtin' => true,
        ],
      ],
    ],
    'getpostdto' => [
      'methodName' => 'actionGetPostdto',
      'paramsMeta' => [
        'dto' => [
          'optional' => false,
          'builtin' => false,
          'type' => 'app\dto\TestDTO',
        ],
      ],
    ],
    'postmixedargs' => [
      'methodName' => 'actionPostmixedargs',
      'paramsMeta' => [
        'c' => [
          'optional' => false,
          'type' => 'string',
          'builtin' => true,
        ],
        'dto' => [
          'optional' => false,
          'builtin' => false,
          'type' => 'app\dto\TestDTO',
        ],
      ],
    ],
    'checkconst' => [
      'methodName' => 'actionCheckconst',
      'paramsMeta' => [
        'c' => [
          'optional' => true,
          'type' => 'string',
          'builtin' => true,
          'default' => app\controller\Test6547586Ctl::SOME_CONSTANT,
        ],
      ],
    ],
  ],
];
