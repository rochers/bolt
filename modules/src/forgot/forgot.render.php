<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class forgot extends \FrontEnd {

	public function render() {
	
		// args
		$args = array(
			'error' => false,
		);
	
		// do they have a token
		if ( p('token') AND p('rid') ) {
		
			// error
			$args['error'] = "Please enter you Email Address and enter a New Password";
		
			// f
			$f = p('f');
		
			// token and rid
			$token = p('token');
			$rid = p('rid');
			$email = $f['email'];
			$pass = $f['pword'];
		
			// is it ok
			if ( p('do') == 'forgot.submit' AND $email AND $pass AND $token AND $rid AND $this->validateFormToken('forgot',p('forgot_token')) ) {
			
				// get the user
				$user = new \dao\user('get',array($email,array('tags'=>array("forgot:{$token}")) ));
			
				// we need a user wit this tag
				if ( $user AND $user->id == $rid ) {
				
					// they're all good 
					// so lets reset their password
					$user->password = \dao\user::encrypt($pass);
				
					// remove the tag
					$user->tags->remove("forgot",$token);
				
					// save the user
					$user->save();
				
					// send them to login
					$this->go( Config::url('login') );
				
				}
				else {
					$args['error'] = "We could not validate your request. Please try again.";
				}
			
			}
		
			// tmpl
			$tmpl = "reset";
		
		}
		else {
		
			// have they submitted the request
			if ( p('do') == 'forgot.submit' ) {
			
				// f
				$f = p('f');
			
				// if email
				if ( $f['email'] AND $this->validateFormToken('forgot',p('forgot_token')) )  {
				
					// try to get this user
					$user = new \dao\user('get',array($f['email']));
					
					// does this user exist
					if ( $user ) {
						
					
						// make them a tok
						$tok = $this->randomString(15);
					
						// url
						$url = Config::url('forgot',array(),array('token'=>$tok,'rid'=>$user->id));
	
						// msg
						$msg = " Go to: {$url} to reset your password. ";
						
						// email
						$this->sendEmail( array(
							'from'=>'no-reply@dailyd.com',
							'to'=>$f['email'],
							'subject'=>"Reset request for DailyD.com",
							'message'=>$msg
							)
						);
						
						// tag their profile with the token
						$user->tags->add("forgot",$tok);
						
						// save
						$user->save();
						
						// error
						$args['error'] = "Please check your email at {$f['email']} for instructions on how to reset your password.";
						
					}
					else {
						$args['error'] = "We didn't recognize the email address provided.";
					}
				
				}
				else {
					$args['error'] = "You must enter an Email Address!";
				}			
			
			}
			
			// template
			$tmpl = "request";
		
		}
		
		// generate a new token
		$args['token'] = $this->generateFormToken('forgot');
		
		// controller	
		return Controller::renderTemplate(
			"forgot/{$tmpl}.template.php",
			$args,
			BOLT_MODULES
		);
		
	
	}

}


?>