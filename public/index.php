<?php
define("APP_PATH",  realpath(dirname(__FILE__) . '/../')); /* 指向 public 的上一级 */
$app  = new Yaf_Application(APP_PATH . "/conf/application.ini");
$app->bootstrap()->run();