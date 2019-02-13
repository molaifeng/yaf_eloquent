<?php

/**
 * @src http://www.laruence.com/manual/yaf.plugin.times.html
 * Class UserPlugin
 */
class UserPlugin extends Yaf_Plugin_Abstract
{

    /**
     * 在路由之前触发，这个是 6 个事件中, 最早的一个. 但是一些全局自定的工作, 还是应该放在 Bootstrap 中去完成
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function routerStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook routerStartup called" . PHP_EOL;
    }

    /**
     * 路由结束之后触发，此时路由一定正确完成, 否则这个事件不会触发
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function routerShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook routerShutdown called" . PHP_EOL;
    }

    /**
     * 分发循环开始之前被触发
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function dispatchLoopStartup(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook DispatchLoopStartup called" . PHP_EOL;
    }

    /**
     * 分发之前触发，如果在一个请求处理过程中， 发生了 forward，则这个事件会被触发多次
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function preDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook PreDispatch called" . PHP_EOL;
    }

    /**
     * 分发结束之后触发，此时动作已经执行结束, 视图也已经渲染完成. 和 preDispatch 类似，此事件也可能触发多次
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function postDispatch(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook postDispatch called" . PHP_EOL;
    }

    /**
     * 分发循环结束之后触发，此时表示所有的业务逻辑都已经运行完成，但是响应还没有发送
     * @param Yaf_Request_Abstract $request
     * @param  Yaf_Response_Abstract $response
     */
    public function dispatchLoopShutdown(Yaf_Request_Abstract $request, Yaf_Response_Abstract $response)
    {
        echo "Hook DispatchLoopShutdown called" . PHP_EOL;
    }
}