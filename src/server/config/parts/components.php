<?php
$components = [

  /*
   * Framework components
   */

  // identity class
  'user' => [
    'class' => yii\web\User::class,
    'identityClass' => app\models\User::class,
  ],
  // logging
  'log' => [
    'targets' => [
      [
        'class' => 'yii\log\FileTarget',
        'levels' => ['trace','info', 'warning','error'],
        'except' => ['yii\*','auth'],
        'logVars' => [],
        //'exportInterval' => 1
      ],
      // errors and exceptions
      [
        'class' => 'yii\log\FileTarget',
        'levels' => ['error'],
        'logFile' => '@runtime/logs/error.log',
        'logVars' => [],
      ]
    ] 
  ],
  // Override http response component
  'response' => [
    'class' => \lib\components\EventTransportResponse::class
  ],
  // Internationalization
  'i18n' => [
    'translations' => [
      'app*' => [
        'class' => yii\i18n\GettextMessageSource::class,
        'basePath' => '@messages',
        'catalog' => 'messages',
        'useMoFile' => false
      ],
    ],
  ],
  // Cache, @todo use more efficient caching
  'cache' => [
    'class' => 'yii\caching\FileCache',
  ],

  /*
   * Composer components
   */

  // Module autoloader
  'moduleLoader' => [
    'class' => bmsrox\autoloader\ModuleLoader::class,
    'modules_paths' => [
      '@app/modules'
    ]
  ],
  
  /*
   * Custom applications components
   */  

  // The application configuration
  'config' => [
    'class' => \lib\components\Configuration::class
  ],    
  'ldap' => require('ldap.php'),
  'ldapAuth'  => [
    'class' => \lib\components\LdapAuth::class
  ],  
  // a queue of Events to be transported to the browser
  'eventQueue' => [
    'class' => \lib\components\EventQueue::class
  ],
  // access manager, handles all things access
  'accessManager' => [
    'class' => \lib\components\AccessManager::class
  ],
  // datasource manager, handles creation and migration of datasource tables
  'datasourceManager' => [
    'class' => \lib\components\DatasourceManager::class
  ],  
  // various utility methods
  'utils' => [ 
    'class' => \lib\components\Utils::class
  ],
  //server-side events, not used
  'sse' => [
    'class' => \odannyc\Yii2SSE\LibSSE::class
  ],
  //message channels, not working yet
  'channel' => [
    'class' => \lib\channel\Component::class
  ]
];
return array_merge(
  // merge db components
  require('db.php'),
  $components
);