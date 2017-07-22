<?php
/**
 * This is auto generated file
 * 
 * Please do not chane it if you are not sure/
 */ 

return [
  'app\controller\Test6547586Ctl' => [
    'postdto' => [
      'methodName' => 'actionPostdto',
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
  ]
];
