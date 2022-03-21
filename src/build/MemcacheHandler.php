<?php
/*--------------------------------------------------------------------------
 | Software: [WillPHP framework]
 | Site: www.113344.com
 |--------------------------------------------------------------------------
 | Author: no-mind <24203741@qq.com>
 | WeChat: www113344
 | Copyright (c) 2020-2022, www.113344.com. All Rights Reserved.
 |-------------------------------------------------------------------------*/
namespace willphp\session\build;
use willphp\config\Config;
/**
 * Memcache session����
 * Class MemcacheHandler
 * @package willphp\session
 */
class MemcacheHandler implements InterfaceSession {
	use Base;
	private $memcache;	
    /**
     * ����
     */
	public function connect() {
		$options = Config::get('session.memcache');
		$this->memcache = new \Memcache();
		$this->memcache->connect($options['host'], $options['port']);
	}
	/**
     * ��ȡ����
     */
	public function read() {
		$data = $this->memcache->get($this->session_id);		
		return $data? json_decode($data, true) : [];
	}
	/**
     * ��������
     */
	public function write() {
		return $this->memcache->set($this->session_id, json_encode($this->items, JSON_UNESCAPED_UNICODE));
	}
	/**
     * ��������
     */
	public function gc() {}
}
