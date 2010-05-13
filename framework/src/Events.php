<?php

class Events extends DatabaseMask {
	
	// proeprties
	protected $db = false;
	private $events = array();
	private static $instance = false;

	/// @breif consturct an events class
	private function __construct() {
	
		// user
		$this->user = Config::get('user');
		
			// loged
			if ( $this->user !== false ) {
				$this->loged = true;
			}
					
		// get a db instance
		$this->db = Database::singleton();
	
	}

	////////////////////////////////
	/// @breif signle way to instance events
	////////////////////////////////	
	public static function singleton() {
		
		if ( !self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
	
		// return me
		return self::$instance;
	
	}
	

	////////////////////////////////
	/// @breif attach an evnet
	////////////////////////////////
	public function on($events,$func,$params=false) {
		
		// string goes to array
		if ( is_string($events) ) {
			$events = array($events);
		}
		
		// events
		foreach ( $events as $event ) {
			
			// no event yet
			if ( !array_key_exists($event,$this->events) ) {
				$this->events[$event] = array();
			}
		
			// function as string to get a sig
			$sig = uniqid();
			
			// attach a callback
			$this->events[$event][$sig] = array('func' => $func, 'params' => $params);
			
		}
		
	}
	
	////////////////////////////////
	/// @breif fire 
	////////////////////////////////	
	public function fire($event,$args) {
		
		// no guid create one
		if ( !isset($args['guid']) ) {
			$args['guid'] = uniqid();
		}		
		
		// no event
		if ( !array_key_exists($event,$this->events) ) {
			return;
		}
		
		// go throguh each and fire them 
		foreach ( $this->events[$event] as $e ) {
			call_user_func($e['func'],$event,$args,$e['params']);
		}
	
		// return the guid
		return $args['guid'];
		
	}

}

?>