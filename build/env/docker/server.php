<?php
//const YII_ENV='dev';
const YII_DEBUG=true;
const BIBUTILS_PATH='/usr/bin/';
require('server/vendor/autoload.php');
require('server/vendor/yiisoft/yii2/Yii.php');
$config = require 'server/config/web.php';
$app = new yii\web\Application($config);
// make sure db connection is opened with utf-8 encoding
$app->db->on(\yii\db\Connection::EVENT_AFTER_OPEN, function ($event) {
  $event->sender->createCommand("SET NAMES utf8")->execute();
});
$app->run();