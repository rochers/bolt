<?php

class Session extends DatabaseMask {

	// private
	private static $instance = false;
	private $cache = false;
	
	// public
	public $loged = false;
	public $logged = false;
	public $uid = false;
	public $sid = false;
	public $user = false;
	public static $me = array();
	

	private function __construct() {

		// connect to the database
		$this->db = Database::singleton();
		$this->cache =  Cache::singleton();
	
		// no session
		if ( Config::get('site/noSession') == true ) {
			return;
		}
		
		// get session
		$this->getSession();
	
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
	
	public static function getUser($key=false) {
		$user = Session::singleton()->user;
		return ( $key ? $user->$key : $user );
	}

	public static function getUid() {
		return Session::singleton()->uid;
	}

	public static function getLogged() {
		return Session::singleton()->logged;
	}

	public function login($email,$pass,$encrypted=false) {
	
		// cookie names
		$a = Config::get('site/cookieUserSession');
		$b = Config::get('site/cookieUserAuth');
	
		// password not encrupted
		if ( !$encrypted ) {
			$pass = \dao\user::encrypt($pass);
		}
	
		// try to get the user
		$user = new \dao\user('get',array($email));
							
		// what up 
		if ( $user->id AND $user->password === $pass ) {
	
			// session
			$sid = $this->md5( uniqid() );
			
			// get as an array
			$row = $user->asArray();
			
			// password
			$row['password'] = $user->password;
			
			// add ip to data
			$row['ip'] = IP;		
			
			// expire
			$expire = time()+(60*60*24);								
			
			// last active 
			$row['last_active'] = time();						
			
			// add their session to the cache
			$resp = $this->cache->set($sid,$row,$expire,'sessions');
		
			// bcookie
			$bcookie = base64_encode(json_encode(array('u'=>$row['id'],'s'=>$sid,'i'=>IP,'e'=>$expire,'c'=>$this->md5($row['password']))));
				
			// set A+B cookie
			setcookie($a,$sid,$expire,'/',COOKIE_DOMAIN,false,true);
			setcookie($b,$bcookie,$expire,'/',COOKIE_DOMAIN,false,true);
		
			// good
			return $row;
		
		}

		// nope
		return false;

	}	
	
	public function logout() {
	
		$this->getSession();

		// cookie names
		$a = Config::get('site/cookieUserSession');
		$b = Config::get('site/cookieUserAuth');

		// remove sid
		$this->cache->delete($this->sid,'sessions');

		// expire
		$expire = time()+1;

		// no cookies
		setcookie($a,false,$expire,'/',COOKIE_DOMAIN,false,true);
		setcookie($b,false,$expire,'/',COOKIE_DOMAIN,false,true);	
		
		$this->loged = false;
		$this->uid = false;
		$this->user = false;
	
	}	
	
	public static function  force($url=false) {
		
		// instance
		$o = self::singleton();
		
		// require
		$o->requireSession($url);
	
	}
	
	public function requireSession($url=false) {
	
		// get a url
		$url = ($url?$url:SELF);	
	
		if ( !$this->logged ) {
			FrontEnd::location( Config::url('login',array(),array('return'=>$url)));
		}	
	
	}
	
	public function getSession() {
	
		if ( $this->loged ) {
			return true;
		}
	
		// cookie names
		$a = Config::get('site/cookieUserSession');
		$b = Config::get('site/cookieUserAuth');
	
		// get cookies
		$acookie = p($a,false,$_COOKIE);
		$bcookie = p($b,false,$_COOKIE);		
	
		// check it 
		if ( $acookie AND $bcookie ) {
			
			// decode the bcookie
			$b = json_decode(base64_decode($bcookie),true);
		
			// check sid, ip and not expired
			if ( $b['s'] == $acookie AND $b['i'] == IP AND $b['e'] > time() ) {						
					
				// get from cache
				$sess = $this->cache->p_get($b['s'],'sessions');			
			
				// not active in over a day
				if ( !isset($sess['last_active']) OR ( isset($sess['last_active']) AND time()-$sess['last_active'] > (60*60*24) ) ) {
					$sess = false;
				}									
			
				// session
				if ( $sess ) {
				
					// data
					$data = $sess;
					
					// ok this is our last check
					// just need to make sure the passwords are ok
					if ( $this->md5($data['password']) == $b['c'] AND $data['ip'] == IP ) {
					
						// loged is true
						$this->loged = $this->logged = true;
						
						// set user
						$this->user = new \dao\user('set',$data);
					
						// user id
						$this->uid = $data['id'];
						
						// sid
						$this->sid = $b['s'];						
						
						// set user with config
						Config::set('loged',true);
						Config::set('user',$this->user);
						Config::set('sid',$this->sid);
						
						// tell controler
						Controller::registerGlobal('_user', $this->user);
					
						// last active 
						$sess['last_active'] = time();
					
						// reset
						$this->cache->set($b['s'],$sess,'sessions');
						
						$this->session = true;
				 								 					
				 					
					}
									
				}
			
			}
		
		}
	
	}

	public function md5($str) {
		return md5("jf89pohij2;3'damiufj".$str."84$89adfaw349408 43a4 038w4r awef aweufh7ao38rhuanwk/ mef");
	}
	
	public static function getMeCookie() {
	
		// get cookie
		$cookie = p_raw(Config::get('site/cookieMe'),"",$_COOKIE);

		// set it 
		$me = json_decode( base64_decode($cookie), true );
	
			// not an array make it 
			if ( !is_array($me) ) {
				$me = array();
			}
			
		self::$me = $me;
			
		return $me;	
	
	}

	public static function setMeValueCond($set,$val=false) {
	
		// try to get it 
		$cur = self::getMeValue($set);
		
		// if not set it 
		if ( !$cur ) {
			self::setMeValue($set,$val);
		}
	
	}
	
	public static function setMeValue($set,$val=false) {
	
		$me = self::getMeCookie();
	
		if ( !is_array($set) ) { $set = array($set=>$val); }
	
		// set key
		foreach ( $set as $k => $v ) {
			$me[$k] = $v;
		}
		
		// set me
		self::$me = $me;
	
		// set me 
		setcookie(Config::get('site/cookieMe'), base64_encode(json_encode($me)),time()+(60*60*24*365),'/',COOKIE_DOMAIN,false,false);				
		
	
	}
		
	// 
	public static function getMeValue($key,$default=false) {
		
		self::getMeCookie();
	
		// me
		$me = self::$me;
	
		if ( array_key_exists($key,$me) ) {
			return $me[$key];
		}
		else {
			return false;
		}
	
	}
	
	public function generateFormToken() {
	
		// what
		$ts = Config::get('site/cookieTokenSession');

		// if yes
		if ( $ts ) {
	
			// do they have a session cookie
			$tsa = p($ts,false,$_COOKIE);
			
			// if no create one
			if ( !$tsa ) {
				
				// create it 
				$sid = FrontEnd::getMd5(uniqid());
				
				// set it 
				setcookie($ts,$sid,false,'/',COOKIE_DOMAIN,false,true);
				
			}
			
			// set it 
			Config::set('token-key',$tsa);
			
		}		
	
	}
	
}

?>