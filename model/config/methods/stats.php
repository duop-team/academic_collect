<?php

$methods = [
  'get' => [
    'params' => [
      [
        'name' => 'student',
        'source' => 'p',
        'required' => true
      ],
      [
        'name' => 'discipline',
        'source' => 'p',
        'required' => true
      ]
    ]
  ],
  'insert' => [
    'params' => [
      [
        'name' => 'student',
        'source' => 'p',
        'required' => true
      ],
      [
        'name' => 'discipline',
        'source' => 'p',
        'required' => true
      ],
      [
        'name' => 'assignment',
        'source' => 'p',
        'required' => true
      ],
      [
        'name' => 'points',
        'source' => 'p',
        'required' => true
      ]
    ]
  ],
  'cache' => [
    'params' => [
      [
        'name' => 'student',
        'source' => 'p', 
        'required' => true
      ]
    ]
  ]
];