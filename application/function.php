<?php

/**
 * 打印数组
 * @param $data
 */
function p($data)
{
    echo '<pre>';
    print_r($data);
    echo '</pre>';
}

/**
 * 获取配置文件信息
 * @param $field
 * @param null $key
 * @return mixed
 */
function getConfig($field, $key = null)
{
    $data = Yaf_Registry::get('config')->toArray();
    return $key ? $data[$field][$key] : $data[$field];
}

/**
 * 获取log路径
 * @return mixed
 */
function getLogPath()
{
    return getConfig('log', 'path');
}

/**
 * 连接redis
 */
function redisConnect()
{
    return Cache_Cache::getInstance('Redis', ['host' => getConfig('session', 'host'), 'port' => getConfig('session', 'port')]);
}

/**
 * 发送邮件
 * @param array $email
 * @param $title
 * @param $content
 * @return bool
 */
function sendmail($email, $title, $content)
{

    if (!is_array($email) || !$email)
        return false;

    set_time_limit(0);
    header("Content-type: text/html; charset=utf-8");

    $title = "=?UTF-8?B?" . base64_encode($title) . "?=";
    $headers = 'MIME-Version: 1.0' . "\r\n";
    $headers .= 'Content-type: text/html; charset=utf-8' . "\r\n"; // Additional headers
    $headers .= 'from:molaifeng@foxmail.com' . "\r\n";

    foreach ($email as $v) {
        if ($v) {
            if (mail($v, $title, $content, $headers)) {
                Log_Log::info('sendmail: success', true, true);
                return true;
            } else {
                Log_Log::info('sendmail: Mailer Error', true, true);
                return false;
            }
        }
    }

}