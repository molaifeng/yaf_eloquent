<?php
/**
 * 日志处理类 
 */
class Log_Log {

	// 日志级别 从上到下，由低到高
	const EMERG = 'EMERG'; // 严重错误: 导致系统崩溃无法使用
	const ALERT = 'ALERT'; // 警戒性错误: 必须被立即修改的错误
	const CRIT = 'CRIT'; // 临界值错误: 超过临界值的错误，例如一天24小时，而输入的是25小时这样
	const ERROR = 'ERROR'; // 一般错误: 一般性错误
	const WARN = 'WARN'; // 警告性错误: 需要发出警告的错误
	const NOTICE = 'NOTIC'; // 通知: 程序可以运行但是还不够完美的错误
	const INFO = 'INFO'; // 信息: 程序输出信息
	const DEBUG = 'DEBUG'; // 调试: 调试信息
	const SQL = 'SQL'; // SQL：SQL语句 注意只在调试模式开启时有效
	
	// 日志信息
	protected static $_log = array();
	
	// 日志存储
	protected static $_storage = null;

	// 日志初始化
	static public function init($config = array()) {
		$type = isset($config['type']) ? $config['type'] : 'File';
		if (strpos($type, '_')) {
			$class = $type;
		} else {
			$class = 'Log_Driver_' . ucwords(strtolower($type));
		}
		unset($config['type']);
		self::$_storage = new $class($config);
	}
	
	/**
	 * DEBUG日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
	 */
	static public function debug($message, $record = false, $save = false, $class = '') {
		self::record($message, self::DEBUG, $record, $save, $class);
	}
	
	/**
	 * INFO日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
	 */
	static public function info($message, $record = false, $save = false, $class = '') {
		self::record($message, self::INFO, $record, $save, $class);
	}
	
	/**
	 * WARN日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
	 */
	static public function warn($message, $record = false, $save = false, $class = '') {
		self::record($message, self::WARN, $record, $save, $class);
	}
	
	/**
	 * ERROR日志输出
     * @static
     * @access public
     * @param string $message 日志信息
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
	 */
	static public function error($message, $record = false, $save = false, $class = '') {
		self::record($message, self::ERROR, $record, $save, $class);
	}
	
	/**
     * 记录日志 并且会过滤未经设置的级别
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param boolean $record  是否强制记录
     * @param boolean $save  是否直接写入
     * @param string  $class 日志类别
     * @return void
     */
	static function record($message, $level = self::ERROR, $record = false, $save = false, $class = '') {
		$traces = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 10);
		$caller = next($traces);
		if ($caller['class'] == 'Log') {
			$caller = next($traces);
			$caller['class'] = !empty($caller['class']) ? $caller['class'] : '';
			$caller['function'] = !empty($caller['function']) ? $caller['function'] : '';
		}

		if ($record || false !== strpos(getConfig('log', 'level'), $level)) {
			if (!is_string($message))
				$message = json_encode($message, JSON_UNESCAPED_UNICODE);

			self::$_log[$class][] = "{$level} [{$caller['class']}.{$caller['function']}] {$message}\r\n";
			if ($save)
				self::save($save);
		}
	}
	
	/**
     * 日志保存
     * @static
     * @access public
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @return void
     */
	static function save($save = false, $type = '', $destination = '') {
		if (empty(self::$_log))
			return;
		
		if (! self::$_storage) {
			$type = $type ? $type : getConfig('log', 'type');
			
			if($save === 2){
				$type="sys";
			}
			$drive = 'Log_Driver_' . ucwords($type);
			self::$_storage = new $drive;
		}
        foreach (self::$_log as $class => $logs) {
            $message = implode('', $logs);
            $destination = '';
            $destination = self::_generateDestination($destination, $class);
            self::$_storage->write($message, $destination);
        }
		
		// 保存后清空日志缓存
		self::$_log = array();
	}
	
	/**
     * 日志直接写入
     * @static
     * @access public
     * @param string $message 日志信息
     * @param string $level  日志级别
     * @param integer $type 日志记录方式
     * @param string $destination  写入目标
     * @param string  $class 日志类别
     * @return void
     */
	static function write($message, $level = self::ERROR, $type = '', $destination = '', $class = '') {
		if (! self::$_storage) {
			$type = $type ? $type : getConfig('log', 'type');
			$drive = 'Log_Driver_' . ucwords($type);
			self::$_storage = new $drive;
		}
		$destination = self::_generateDestination($destination, $class);
		self::$_storage->write("{$level}: {$message}", $destination);
	}

    /**
     * 生成写入目标， 如果没有指定，给出一个默认的目标
     * 
     * @param  string $destination 写入目标
     * @param  string $class       类别
     * 
     * @return string
     */
    static private function _generateDestination($destination = '', $class = '')
    {
        if (!empty($destination)) {
            return $destination;
        }
        $destination = date('y_m_d') . '.log';;
        if ($class != '') {
            $destination = $class . '_' . $destination;
        }
        return $destination;
    }
}