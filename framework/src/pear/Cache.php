<?php

class Cache {

	// instance
	private static $instance = false;

	/// @breif private construct from internal onlu
	private function __construct() {
		
		// base_ns
		// we add the host so that dev boxes
		// have their own cache namespace
		$this->basens = Config::get('cache/prefix').":";
	
		// $mem
		$this->memcache = new Memcache;
		
		// host
		$hosts = explode(',',Config::get('cache/host')); 
	
		// add our servers
		foreach ( $hosts as $host ) { 
			$this->memcache->addServer($host,'11211');
		}
	
	}

	public static function singleton() {
		
		// if none, create one
		if ( !self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
	
		// give back
		return self::$instance;
	
	}

	// get 
	public function get($key,$ns='default') {
	
		// cid
		$cid = "{$this->basens}:{$ns}:{$key}";
		
		// turn caching off in dev
		if ( DevMode ) {
			return false;
		}
		
		// set it 
		return $this->memcache->get($cid);
	
	}
	
	// persisstent get will get no matter what setting
	public function p_get($key,$ns='default') {
	
		// cid
		$cid = "{$this->basens}:{$ns}:{$key}";
		
		// set it 
		return $this->memcache->get($cid);
	
	}	
	
	// set
	public function set($key,$value,$exp=0,$ns='default') {
	
		// cid
		$cid = "{$this->basens}:{$ns}:{$key}";
	
		// set it 
		$r = $this->memcache->set($cid,$value,MEMCACHE_COMPRESSED,(int)$exp);
	
		// if ns does not exists we need to keep track
		if ( $ns != 'default' ) {
			
			// cid
			$cid = "{$ns}:__keys";
			
			// get keys
			$cur = $this->get($cid);
			
			// add this key
			$cur[] = $key;
			
			// set it
			$this->set($cid,$cur);
			
		}
	
		// give back the value
		return $value;
	
	}
	
	// delete
	public function delete($key=false,$ns='default') {
	
		// no key
		if ( !$key OR $key == '*' ) {
		
			// cid
			$cid = "{$ns}:__keys";
			
			// get keys
			$cur = $this->get($cid);
			
			// loop me
			if ( is_array($cur) ) {
				foreach ( $cur as $k ) {
					try {
					//	$this->memcache->delete("{$this->basens}:{$ns}:{$k}");
					}
					catch(Exception $e) {}
				}		
			}
		
		}
		else {
		
			// remove the key
			$cid = "{$this->basens}:{$ns}:{$key}";
		
			// remove
			$this->memcache->replace($cid,false,false,1);
			
		}		
		
		// done
		return true;
	
	}



}


?>