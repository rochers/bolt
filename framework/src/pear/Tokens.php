<?php

class Tokens extends DatabaseMask {

	private static $instance = false;

	private static $types = array();

	// no construct
	private function __construct() {
		
		// database
		$this->db = Database::singleton();					
		$this->cache = Cache::singleton();
		$this->event = Events::singleton();
		
		// hook into pre-route
		$this->event->on('get-page',array($this,'_validate'));
		
	}
	
	public function _validate($page,$args) {
		
		// page is token
		if ( trim($args['path'],'/') != 'token' ) {
			return false;
		}
	
		// get the t and s
		$t = p('t');
		$s = p('s');
	
		// not 
		if ( !$t OR !$s OR $s != FrontEnd::getMd5($t) ) {
			die('bad 1');
		}
		
		// decompress the token
		$t = json_decode( base64_decode( p('t') ), true );
		
		// need three parts
		if ( !is_array($t) OR ( is_array($t) AND count($t) != 3 ) ) {
			die('bad 2');
		}
		
		// make sure we have this type
		if ( isset($t[0]) AND !array_key_exists($t[0],self::$types) ) {
			die('bad 3');
		}
		
		// id
		$id = $t[2];
	
		// try to get the token
		$tok = $this->row("SELECT * FROM `tokens` WHERE `id` = ? ",array($id));
		
			// no token
			if ( !$tok ) {
				die('bad 4');
			}
	
		$token = json_decode( $tok['meta'], true );
		
		// now make sure everything matches up
		if ( !$token OR ( $token AND ( $token['type'] != $t[0] OR $token['user'] != $t[1] ) ) ) {
			die('bad 5');
		}
		
		// remove
		$this->query(" DELETE FROM `tokens` WHERE `id` = ? LIMIT 1 ",array($id));
		
		// param
		$params = array(
			'id' => $token['asset'],
			'user' => $token['user'],
			'name' => $token['name']
		);
	
		// token is all good
		// lets try to figure out the type
		call_user_func( self::$types[$token['type']], $params );
	
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
	
	public static function generate($cfg) {
	
		// generate
		return self::singleton()->_generate($cfg);
	
	}

	public function _generate($cfg) {

		// id
		$id = uniqid();
		
		// user
		$u = Session::getUser();		
		
		// make them a unique token
		$token = base64_encode( json_encode( array(
			$cfg['type'],
			$u->id,
			$id
		)));
		
		// sig
		$sig = FrontEnd::getMd5( $token );
		
		// make them a url
		$url = Config::url('token',array(),array('t'=>$token,'s'=>$sig));
	
		// replace
		$msg = str_replace("{url}",$url,$cfg['msg']);	
	
		// add this token to the meta
		$t = array( 
			'email' => $cfg['to'], 
			'sent' => utctime(), 
			'user' => $u->id, 
			'name' => $u->name, 
			'type' => $cfg['type'], 
			'asset' => $cfg['id'] 
		);
	
		// send it 
		$r = FrontEnd::doSendEmail(array(
			'form'		=> 'no-reply@dailyd.com',
			'to'		=> $cfg['to'],
			'subject' 	=> $cfg['subject'],
			'message' 	=> $msg
		));
	
		// save the token
		$this->query("INSERT INTO `tokens` SET `id` = ?, `meta` = ? ",
			array(
				$id,
				json_encode($t)
			)
		);
	
		// return the id
		return $id;
	
	}

	public static function register($type,$func) {
		return self::singleton()->_register($type,$func);
	}

	public function _register($type,$func) {
		
		// add the type to types
		self::$types[$type] = $func;	
	
	}

}

?>