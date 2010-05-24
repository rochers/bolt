<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;

class login extends \FrontEnd {

	public function render($cfg) {
	
		// args
		$args = array(
			'error' => false,
			'f' => array(
				'email' => false
			),
			'fbLogin' => p('fbLogin',false,$cfg)
		);
			
		
		// check for a submit request
		if ( p('do') == 'login.submit' ) {
			
			// form
			$form = p('f');
			
			// email and password
			$e = $args['f']['email'] = $form['email'];
			$p = $form['pword'];
			
			// if everything is ok
			if ( $e AND $p AND $this->validateFormToken('login',p('login_token')) ) {
				
				// logout
				$this->session->logout();
				
				// what up
				if ( $this->session->login($e,$p) !== false ) {
				
					// go home
					if ( p('xhr') ) {
						$this->printJsonResponse(array( 'do' => 'redi', 'url' => '/'));
					}
					else {
						
						if ( isset($cfg['after']) ) { 
							$this->go($cfg['after']);
						} else { 
							$this->go('/');
						}
						
					}				
	
				
				}
		
			}
			
			// error
			$args['error'] = "Invalid Login";		
			
		}
		else if ( p('auth') == 'facebook' ) {

			
			// check with facebook to see if they're loged in	
			$fbuser = \Fb::singleton()->getUser();
		
			// facebook user
			if ( $fbuser ) { 
			
				// name
				$u = \Fb::singleton()->api('/me');			
			
				// try to get a user to see 
				// if they've already created an account
				$user = new \dao\user('get',array($u['email']));		
								
				if (!$user->tags) { $user->tags = new \dao\tags(); }
				
				// pick
				$user->profile_pic = "https://graph.facebook.com/{$u['id']}/picture?type=square";
				
				// fb profile
				$user->profile_fb = $u;											
				
				// no user we have to send them to register
				if ( !$user->id ) {
					
					// name
					$name = explode(' ',$u['name']);
				
					// make their password
					$user->password = \dao\user::encrypt( time() . uniqid() ); 
					$user->email = $u['email'];
					$user->firstname = array_shift($name);
					$user->lastname = implode(' ',$name);
					
					// area
					$area = Session::getMeValue('area');				
										
					// add fb tag
					$user->tags->add( 'fb',$fbuser );
					
					// save
					$user->save();						
					
					// fire event
					$this->event->fire('reg',array(
						'user' => $user,
						'from' => 'fb-login'
					));					
									
				}
				
				// log them in
				$this->session->login($user->email,$user->password,true);				
				
				// no fb tag for this user
				if ( !$user->tags->get('fb',$fbuser) ) {
					
					// add a facebook tag to their account
					$user->tags->add( 'fb',$fbuser );
										
				} 
				
				// save
				$user->save();				
				
				
				// go home
				if ( p('xhr') ) {
					$this->printJsonResponse(array( 'do' => 'redi', 'url' => '/'));
				}
				else {
					
					if ( isset($cfg['after']) ) { 
						$this->go($cfg['after']);
					} else { 
						$this->go('/');
					}
					
				}									
			
			}
			
		}

		// generate a new token
		$args['token'] = $this->generateFormToken('login');
		
		// controller	
		return Controller::renderTemplate(
			"login/box.template.php",
			$args,
			BOLT_MODULES
		);
	
	}

}


?>