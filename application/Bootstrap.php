<?php

use Illuminate\Database\Capsule\Manager as Capsule;

class Bootstrap extends Yaf_Bootstrap_Abstract
{

    public function _initLoader()
    {
        Yaf_Loader::import(APP_PATH . "/vendor/autoload.php");
    }

    public function _initConfig()
    {
        $config = Yaf_Application::app()->getConfig();
        Yaf_Registry::set("config", $config);
    }

    public function _initDefaultName(Yaf_Dispatcher $dispatcher)
    {
        $dispatcher->setDefaultModule("Index")->setDefaultController("Index")->setDefaultAction("index");
    }

    public function _initDatabaseEloquent()
    {
        $config = Yaf_Application::app()->getConfig()->database->toArray();
        $capsule = new Capsule;

        // 创建链接
        $capsule->addConnection($config);

        // 设置全局静态可访问
        $capsule->setAsGlobal();

        // 启动Eloquent
        $capsule->bootEloquent();

    }

//    public function _initRoute(Yaf_Dispatcher $dispatcher)
//    {
//        $router = $dispatcher->getRouter();
//        $router->addRoute("name", new Yaf_Route_Simple("m", "c", "a"));
//        $router->addRoute("name", new Yaf_Route_Supervar('r'));
//        //$router->addRoute("name", new Yaf_Route_Map(true));
//    }
}