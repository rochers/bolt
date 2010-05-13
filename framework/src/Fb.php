<?php

require(FRAMEWORK . "fb/facebook_SDK.php");

////////////////////////////////
/// @breif facebook integration
////////////////////////////////
class Fb {

	// properties
	public $fb = false;
	public static $instance = false;
	private $loged = false;
	private $user = false;

	////////////////////////////////
	/// @breif singleton constructor
	////////////////////////////////
	public static function singleton() {
	
		if ( !self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
	
		// return
		return self::$instance;
	
	}
	

	////////////////////////////////
	/// @breif private constrcutor
	////////////////////////////////
	private function __construct() {
		
		// fb 
		$this->fb = new Facebook(array('appId'=>Config::get('site/fb-key'),'secret'=>Config::get('site/fb-secret'),'cookie'=>true));		
		
		// user
		$this->user = Config::get('user');
		
			// loged
			if ( $this->user !== false ) {
				$this->loged = true;
			}		
		
		$this->fbSession = $this->fb->getSession();
		
		// attach some events
		$events = Events::singleton();
		
		// what we want to listen for
		$e = array('form-enter');
		
		// on
		$events->on($e,array($this,'eventDispatch'));
		
	}


	////////////////////////////////
	/// @breif event dispatcher
	////////////////////////////////	
	public function eventDispatch($type,$args) {
	
		// switch me on type
		switch($type) {
			
		
		}
	
	}


	/////////////////////////////////////
	/// @breif magic caller that passes 
	///		   any undefined methods to 
	///		   to api_client
	/////////////////////////////////////	
	public function __call($name,$args=false) {	
		if ( method_exists($this->fb,$name) ) {
			return call_user_func_array(array($this->fb,$name),$args);
		}
	}

	////////////////////////////////
	/// @breif shortcut to stream_publish
	////////////////////////////////	
	public function streamPublish($args) {

		// push through
		$this->fb->api_client->stream_publish(
			$args['message'],
			json_encode($args['attachment']),
			json_encode($args['action'])
		);		
	
	}	

	////////////////////////////////
	/// @breif shortcut to get_loggedin_user
	////////////////////////////////
	public function getUser() {
		return @$this->fb->getUser();
	}
	
	
	////////////////////////////////
	/// @breif shortcut to notifications_sendEmail
	////////////////////////////////	
	public function sendEmail($subject,$message,$to=false) {
		
		// no to
		if ( !$to ) {
			$to = $this->user['fbtoken'];
		}
		
		// no an array
		if ( !is_array($to) ) {
			$to = array($to);
		}

		// send it 		
        $this->fb->api_client->notifications_sendEmail(
                $to,						// userids
                $subject,
                $message,					// plaintext
                nl2br($message,true)		// fbml version. since we don't have one we use the same
            );   		
	
	}
	

}

?>