<?php

/**
 * 在此类中做404跳转
 * Class ErrorController
 */
class ErrorController extends Yaf_Controller_Abstract
{

    public function init()
    {
        header("Content-Type:text/html;charset=utf-8");
        Yaf_Dispatcher::getInstance()->disableView();
    }

    public function errorAction()
    {

        $exception = $this->getRequest()->getException();

        // 此处可扩展，当接口遇到错误并停止时，会写条日志的
        Log_Log::info('error:' . $exception->getMessage() .'|file:'.$exception->getFile().'|line:'.$exception->getLine(), true, true, 'error_log');

        if (isset($_GET['x'])) {
            echo "<pre>";
            var_dump($exception);
            echo "</pre>";
        }

        // 加载失败
        if ($exception instanceof Yaf_Exception_LoadFailed) {
            $this->redirect('/404');return;

            // 其他错误
        } else {
        }
    }
}
