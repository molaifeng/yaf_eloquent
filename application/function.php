<?php

use Illuminate\Database\Capsule\Manager as DB;

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
    return Cache_Cache::getInstance('Redis', ['host' => getConfig('redis', 'host'), 'port' => getConfig('redis', 'port')]);
}

/**
 * 取得对象实例 支持调用类的静态方法
 * @param string $name 类名
 * @param string $method 方法名，如果为空则返回实例化对象
 * @param array $args 调用参数
 * @return object
 */
function get_instance_of($name, $method='', $args=array())
{
    static $_instance = array();
    $identify = empty($args) ? $name . $method : $name . $method . to_guid_string($args);
    if (!isset($_instance[$identify])) {
        if (class_exists($name)) {
            $o = new $name();
            if (method_exists($o, $method)) {
                if (!empty($args)) {
                    $_instance[$identify] = call_user_func_array(array(&$o, $method), $args);
                } else {
                    $_instance[$identify] = $o->$method();
                }
            }
            else
                $_instance[$identify] = $o;
        }
        else
            halt('实例化一个不存在的类！' . ':' . $name);
    }
    return $_instance[$identify];
}

/**
 * 错误输出
 * @param $msg
 * @param int $code
 * @param string $type
 */
function throwException($msg, $code = 2, $type = 'RuntimeException')
{
    throw new $type($msg, $code);
}

/**
 * 根据PHP各种类型变量生成唯一标识号
 * @param mixed $mix 变量
 * @return string
 */
function to_guid_string($mix)
{
    if (is_object($mix) && function_exists('spl_object_hash')) {
        return spl_object_hash($mix);
    } elseif (is_resource($mix)) {
        $mix = get_resource_type($mix) . strval($mix);
    } else {
        $mix = serialize($mix);
    }
    return md5($mix);
}


/**
 * 发送邮件
 * @param array $email
 * @param $title
 * @param $content
 * @return bool
 */
function sendmail1($email, $title, $content)
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

/**
 * 发送邮件
 * @param $data
 * @return array
 */
function sendmail($data)
{

    require_once APP_PATH . "/application/library/Mail/PHPMailerAutoload.php";

    $mail = new PHPMailer(true);
    $result = [
        'status'    => 1,
        'msg'       => '发送成功'
    ];

    try {
        $mail->setFrom(getConfig('mail', 'from'));
        if (isset($data['to']) && $data['to']) {
            foreach (explode(',', $data['to']) as $tv) {
                $mail->addAddress($tv);
            }
        }

        if (isset($data['cc']) && $data['cc']) {
            foreach (explode(',', $data['cc']) as $cv) {
                $mail->addCC($cv);
            }
        }

        if (isset($data['attachment']) && !empty($data['attachment'])) {
            foreach ($data['attachment'] as $av) {
                $mail->addAttachment(APP_PATH . DIRECTORY_SEPARATOR . 'public' . $av['path']);
            }
        }
        $mail->isHTML(true);
        $mail->CharSet = 'utf-8';
        $mail->Subject = $data['subject'];
        $mail->Body    = $data['content'];
        $mail->send();
    } catch (Exception $e) {
        $result = [
            'status'    => 0,
            'msg'       => $mail->ErrorInfo
        ];
        Log_Log::info("send mail failure:" . var_export($result, 1), 1, 1, 'mail_error');
    }

    return $result;

}

/**
 * 自动转换字符集 支持数组转换
 * @param $fContents
 * @param $from
 * @param $to
 * @return array|mixed|string
 */
function auto_charset($fContents, $from, $to)
{

    $from = strtoupper($from) == 'UTF8' ? 'utf-8' : $from;
    $to = strtoupper($to) == 'UTF8' ? 'utf-8' : $to;

    if (strtoupper($from) === strtoupper($to) || empty($fContents) || (is_scalar($fContents) && !is_string($fContents))) {

        // 如果编码相同或者非字符串标量则不转换
        return $fContents;
    }

    if (is_string($fContents)) {
        if (function_exists('mb_convert_encoding')) {
            return mb_convert_encoding($fContents, $to, $from);
        } elseif (function_exists('iconv')) {
            return iconv($from, $to, $fContents);
        } else {
            return $fContents;
        }
    } elseif (is_array($fContents)) {
        foreach ($fContents as $key => $val) {
            $_key = auto_charset($key, $from, $to);
            $fContents[$_key] = auto_charset($val, $from, $to);
            if ($key != $_key)
                unset($fContents[$key]);
        }
        return $fContents;
    } else {
        return $fContents;
    }
}

/**
 * 根据字段重组数组
 * @param array $array 数组 默认为 array()
 * @param string $field 字段名 默认为id
 * @return array $result 重组好的数组
 */
function setArrayByField($array = array(), $field = 'id')
{
    $result = array();
    if (is_array($array)) {
        foreach ($array as $key => $value) {
            $result[$value[$field]] = $value;
        }
    }
    return $result;
}

/**
 * 获取数组字段值
 * @param array $array 数组 默认为 array()
 * @param string $field 字段名 默认为id
 * @return array $result 数组(各字段值)
 */
function getValueByField(array $array, $field = 'id')
{
    $result = array();
    if (function_exists('array_column')) {
        $result = array_column($array, $field);
    } else {
        foreach ($array as $key => $value) {
            $result[] = $value[$field];
        }
    }
    return $result;
}


/**
 * 通过关联数组获取数据
 * @param string $table 表名
 * @param string $tableField 表字段
 * @param array $array 数组
 * @param string $arrayField 数组的字段
 * @param array $getField 要获取的字段 此字段也可以是 *
 * @param string $connection 数据库连接
 *
 * @return array $result 获取的数据
 */
function getDataByArray($table, $tableField, $array, $arrayField, $getField = [], $connection = '') {
    $result = empty($connection) ?
        DB::table($table)->select($getField)->whereIn($tableField, getValueByField($array, $arrayField))->get() :
        DB::connection($connection)->table($table)->select($getField)->whereIn($tableField, getValueByField($array, $arrayField))->get();
    return setArrayByField($result, $tableField);
}

/**
 * 获取用户IP
 * @return string
 */
function get_client_ip()
{
    $IP = '';
    if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
        $IP = getenv('HTTP_CLIENT_IP');
    } elseif(getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
        $IP = getenv('HTTP_X_FORWARDED_FOR');
    } elseif(getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
        $IP = getenv('REMOTE_ADDR');
    } elseif(isset($_SERVER['REMOTE_ADDR']) && $_SERVER['REMOTE_ADDR'] && strcasecmp($_SERVER['REMOTE_ADDR'], 'unknown')) {
        $IP = $_SERVER['REMOTE_ADDR'];
    }
    return $IP ? $IP : "unknow";
}
