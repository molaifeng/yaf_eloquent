# 集成了 Laravel 的 Eloquent ORM 的 Yaf 框架
![Supported PHP versions: >=5.4](https://img.shields.io/badge/PHP-%3E%3D5.4-blue.svg)
![Supported Yaf versions: >=1.8.0](https://img.shields.io/badge/Yaf-%3E%3D2.3.2-orange.svg)
![Supported Eloquent versions: 5.0](https://img.shields.io/badge/Eloquent-%205.0-green.svg)
![License](https://img.shields.io/badge/license-Apache%202-yellow.svg)

# Session由默认的文件改为Redis存储

```
public function _initSession()
{
    try {
        $redis = redisConnect();
        $redis->ping();
        $session = new Util_Session();
        session_set_save_handler($session, true);
    } catch (Exception $e) {
        Log_Log::info('[Bootstrap] session init error:' . $e->getMessage(), true, true);
    }
}
```

# 多个数据库链接操作如下

```

// 默认的
DB::table('tb_name')->get()

// another
DB::connection('another')->get();

```

# models 子目录使用

```

// 命名空间写法
$user = new \Sub\UserModel();
echo $user->hello();

// 传统写法
$demo = new Sub_DemoModel();
echo $demo->hello();
```

# 文件上传

```
// 上传目录
$savePath = getConfig('upload', 'path');

// 允许的规则
$allowType = getConfig('upload', 'rule');

$result = parent::upload($allowType, $savePath);
```

# 邮件发送

```

# 首先安装sendmail模块
yum -y install sendmail  
/etc/rc.d/init.d/sendmail start

// 发送邮件，可群发
sendmail([
    'to'        => [], // 邮件发送人列表
    'cc'        => [], // 邮件抄送人列表
    'subject'   => '', // 邮件主题
    'content'   => '', // 邮件正文
    'attachment'=> []  // 附件列表
]);
```

# 数据加解密

```

$string = '数据加解密';
$crypt = new Util_CryptAES();
$crypt->set_key(getConfig('CryptAES', 'key'));
$crypt->require_pkcs5();

// 加密
$crypt_string = $crypt->encrypt($string);

// 解密
$decrypt_string = $crypt->decrypt($crypt_string); 

echo $crypt_string . ' ' . $decrypt_string; // 1MxgJsgKZKXXhTE8msOKpA== 数据加解密

// 此类还可以配合Java来进行加解密，具体链接可参考 http://www.cnblogs.com/yipu/articles/3871576.html
```

# 日志记录

```

// 直接记录在以日期开头的文件里，如16_08_24.log
Log_Log::info('this is a log', true, true);

// 加上前缀，prefix_16_08_24.log
Log_Log::info('this is a log', true, true, 'prefix');
```

# Curl 操作

```

$curl = new \Http\Curl();

// get
$curl->get('https://www.example.com/search', array(
    'q' => 'keyword',
));

// post
$curl->post('https://www.example.com/login/', array(
    'username' => 'myusername',
    'password' => 'mypassword',
));

// more https://github.com/php-curl-class/php-curl-class
```

# 全局异常捕获

```

try {
    if ($_POST['test']) {
    
    }
} catch (Exception $e) {
    echo $e->getMessage(); // Undefined index: test
}


```

# URL 重写

```
public function _initRoute(Yaf_Dispatcher $dispatcher)
{
    $router = $dispatcher->getRouter();
    
    // http://yaf/login
    $router->addRoute('login', new Yaf_Route_Rewrite(
        '/login$',
        array(
            'module'         => 'Index', // 默认的模块可以省略
            'controller'    => 'Public',
            'action'        => 'login'
        )
    ));
    
    // http://yaf/logout
    $router->addRoute('logout', new Yaf_Route_Rewrite(
        '/logout$',
        array(
            'controller'    => 'Public',
            'action'        => 'logout'
        )
    ));
    
    // http://yaf/404
    $router->addRoute('404', new Yaf_Route_Rewrite(
        '/404$',
        array(
            'controller'    => 'Public',
            'action'        => 'unknow'
        )
    ));
}
```
