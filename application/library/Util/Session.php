<?php

/**
 * customize session for redis
 * Class Util_Session
 */
class Util_Session implements SessionHandlerInterface
{

    private $handle;
    private $lifetime;
    private $prefix = 'YAF_ELOQUENT:';

    /**
     * open session
     * @param string $save_path
     * @param string $session_name
     * @return bool
     */
    public function open($save_path, $session_name)
    {
        $this->handle = redisConnect();
        $this->lifetime = ini_get('session.gc_maxlifetime');
    }

    /**
     * close session
     * @return bool
     */
    public function close()
    {
        $this->gc($this->lifetime);
        $this->handle->close();
        $this->handle = null;
        return true;
    }

    /**
     * read session by session_id
     * @param string $session_id
     * @return mixed
     */
    public function read($session_id)
    {
        $session_id = $this->prefix . $session_id;
        $data = $this->handle->get($session_id);
        return $data;
    }

    /**
     * write session by session_id
     * @param string $session_id
     * @param string $session_data
     * @return mixed
     */
    public function write($session_id, $session_data)
    {
        $this->handle->set('chk_' . $session_id, '', $this->lifetime);
        return $this->handle->set($this->prefix . $session_id, $session_data, $this->lifetime);
    }

    /**
     * delete session_id
     * @param string $session_id
     * @return mixed
     */
    public function destroy($session_id)
    {
        return $this->handle->delete($this->prefix . $session_id);
    }

    /**
     * this function is no use because of redis expire
     * @param int $maxlifetime
     * @return bool
     */
    public function gc($maxlifetime)
    {
        return true;
    }

}