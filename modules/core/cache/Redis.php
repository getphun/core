<?php
/**
 * Core redis driver
 * @package core
 * @version 0.0.1
 * @upgrade true
 */

namespace Core\Cache;

class Redis
{
	private $conn;

	public function __construct($opt){
		$this->conn = new \Redis();

		if(!$this->conn->connect($opt['host'], $opt['port']))
			throw new \Exception('Unable to connect to redis server');

		if($opt['password']){
			if(!$this->conn->auth($opt['password']))
				throw new \Exception('Invalid redis password');
		}

		if(!$this->conn->select($opt['dbindex']))
			throw new \Exception('Invalid redis db index');
	}

	public function get($name){
		if(!$this->conn)
			return false;
		$value = $this->conn->get($name);
		if(!$value)
			return false;
		return unserialize($value);
	}

	public function remove($name){
		if(!$this->conn)
			return false;
		return (bool)$this->conn->delete($name);
	}

	public function save($name, $content, $expiration){
		if(!$this->conn)
			return false;
		return $this->conn->setEx($name, $expiration, serialize($content));
	}

	public function total(){
		if($this->conn)
			return (int)$this->conn->dbSize();
		return 0;
	}

	public function truncate(){
		if($this->conn)
			$this->conn->flushDb();
		return true;
	}

}