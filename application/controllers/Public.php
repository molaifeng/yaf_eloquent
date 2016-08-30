<?php

class PublicController extends Yaf_Controller_Abstract
{

    /**
     * 404 not found
     */
    public function unknowAction()
    {
        die('Oops,你访问的页面不存在!');
    }

}