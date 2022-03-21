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
use willphp\cookie\Cookie;
/**
 * Trait Base
 * @package willphp\session\build
 */
trait Base {
	protected $session_id; //session ID
	protected $session_name; //session ����
	protected $expire; //����ʱ��
	protected $items = []; //session ����
	static protected $startTime; //��ʼʱ��
    /**
     * SESSION ������
     * @return $this
     */	
	public function bootstrap()	{
		$this->session_name = Config::get('session.name', 'WILLPHPID');
		$this->expire = intval(Config::get('session.expire', 864000));
		$this->session_id = $this->getSessionId();		
		$this->connect();
		$content = $this->read();		
		$this->items = is_array($content) ? $content : [];		
		if (is_null(self::$startTime)) {
			self::$startTime = microtime(true);
		}		
		return $this;
	}
    /**
     * ��ȡSESSION_ID
     * @return string
     */	
	final protected function getSessionId() {
		$id = Cookie::get($this->session_name);
		if (!$id) {
			$id = 'willphp'.md5(microtime(true).mt_rand(1, 6));
		}
		Cookie::set($this->session_name, $id, $this->expire, '/', Config::get('session.domain'));		
		return $id;
	}
    /**
     * ����Ƿ����
     * @param $name
     * @return bool
     */	
	public function has($name) {
		return isset($this->items[$name]);
	}
    /**
     * ��������
     * @param $data
     */
	public function batch($data) {
		foreach ($data as $k => $v) {
			$this->set($k, $v);
		}
	}
    /**
     * ��������
     * @param string $name  ����(֧������.����)
     * @param mixed  $value ֵ
     * @return mixed
     */
	public function set($name, $value) {
		$tmp  = &$this->items;
		$exts = explode('.', $name);
		if (is_array($exts) && ! empty($exts)) {
			foreach ($exts as $d) {
				if ( ! isset($tmp[$d])) {
					$tmp[$d] = [];
				}
				$tmp = &$tmp[$d];
			}			
			$tmp = $value;			
			return true;
		}
		return false;
	}
    /**
     * ��ȡsession����
     * @param string $name ����(֧������.����)
     * @param string $value Ĭ��ֵ
     * @return null
     */
	public function get($name = '', $value = null) {
		$tmp = $this->items;
		$arr = explode('.', $name);
		foreach ((array)$arr as $d) {
			if (isset($tmp[$d])) {
				$tmp = $tmp[$d];
			} else {
				return $value;
			}
		}		
		return $tmp;
	}
    /**
     * ɾ��
     * @param $name
     * @return bool
     */
	public function del($name) {
		if (isset($this->items[$name])) {
			unset($this->items[$name]);
		}		
		return true;
	}
    /**
     * ��ȡ����
     * @return mixed
     */
	public function all() {
		return $this->items;
	}
    /**
     * �������(�������ݲ�ɾ��)
     * @return bool
     */
	public function flush() {
		$this->items = [];		
		return true;
	}
    /**
     * �������
     * @param        $name
     * @param string $value
     * @return bool|mixed|void
     */
	public function flash($name = '', $value = '') {
		if (is_array($name)) {		
			foreach ($name as $name => $value) {
				$this->set('_FLASH_.'.$name, [$value, self::$startTime]);
			}			
			return;
		} elseif ($name === '') {			
			return $this->get('_FLASH_', []);
		} elseif (is_null($name)) {			
			return $this->del('_FLASH_');
		}
		if (is_null($value)) {
			if (isset($this->items['_FLASH_'][$name])) {
				unset($this->items['_FLASH_'][$name]);
			}
		} elseif ($value === '') {
			$data = $this->get('_FLASH_.'.$name);
			return isset($data[0])? $data[0] : '';			
		} 
		return $this->set('_FLASH_.'.$name, [$value, self::$startTime]);
	}
    /**
     * �����Ч����
     */
	public function clearFlash() {
		$flash = $this->flash();
		foreach ($flash as $k => $v) {
			if ($v[1] != self::$startTime) {
				$this->flash($k, null);
			}
		}
	}
    /**
     * �ر�д��Ự����,ͬʱִ����������
     */
	public function close() {
		$this->write();
		if (mt_rand(1, 100) == 1) {
			$this->gc();
		}
	}
    /**
     * ��������
     */
	public function __destruct() {
		$this->clearFlash();
		$this->close();
	}
}