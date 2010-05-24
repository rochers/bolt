<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class reg extends \FrontEnd {
	
	// args
	public $args = array();
	
	// construct our class
	public function __construct($args) {
	
		parent::__construct();
	
		// set args
		$this->args = $args;
		
	}

	// render
	public function render($cfg) {		
				
		// register our arg
		Forms::$arg = "reg";
		
		// our form to start
		$form = array(
			'name' => array("Name",array('name'=>'name')),
			'email' => array("Email Address",array('name'=>'email')),
			'password' => array("Password",array('name'=>'pword',"type"=>"password"))
		);
		
		// did the want more
		if ( isset($cfg['fields']) ) {
			foreach ( $cfg['fields'] as $key => $field ) {
				if ( $field == false ) {
					unset($form[$key]);
				}
				else {
					$form[$key] = $field;
				}
			}
		}
		
		$args = array(
			'form' => $form,
			'error' => false,
			'action' => SELF
		);
		
			if ( isset($cfg['action']) ) {
				$args['action'] = $cfg['action'];
			}
		
		// submit
		if ( p('do') == 'reg.submit' ) {
		
			// error
			$error = false;
			
			// fields
			$f = p('reg');
		
			// validate the form
			foreach ( $form as $id => $field ) {
			
				$func = array("Forms","validate");
			
				// check if there's a custom
				// validator defined
				if ( isset($field[1]['validate']) && isset($this->args['template']) ) {
					$func = $this->args['template'][$field[1]['validate']];
				}
				
				// call validator
				$r = call_user_func($func,$field[1],$f);
							
				// was there an error				
				if ( $r === false ) {
					$error .= "<div>{$field[0]} is blank!</div>";
				}
				
				// set the value
				$form[$id][1]['value'] = $f[$field[1]['name']] = $r;	

			}
			
			if ( !$this->validateFormToken('reg',p_raw('reg_token') ) ) {
				$error .= "<div>Something went wrong. Please try re-submitting.</div>";
			}
			
			// no erro
			if ( $error === false ) {
			
				// check if there's a user with this email address
				$user = new \dao\user('get',array($f['email']));
			
				// yes
				if ( !$user->id ) {

					// fv
					$fv = $f;
				
					// make a name
					$name = explode(' ',$f['name']);
					
					$user->set(array(
						'email' => $f['email'],
						'firstname' => array_shift($name),
						'lastname' => implode(' ',$name),					
						'password' => \dao\user::encrypt($f['pword'])
					));
				
					// everything else goes into profile
					unset($f['pword'],$f['email'],$f['name']);
					
					// each 
					$profile = new \StdClass();
										
					foreach ( $f as $key => $val ) {
						$profile->{$key} = $val;
					}
					
					$user->profile = $profile;
			
					// any tags
					if ( isset($cfg['tags']) )	{
						foreach ( $cfg['tags'] as $fid => $group ) {					
							$user->tags->add( $group,$f[$fid] );
						}
					}
										
					// save me 
					$resp = $user->save();
				
					// what 
					if ( $resp ) {
												
						// fire event
						$this->event->fire('reg',array(
							'user' => $user,
							'from' => 'module'
						));
						
						// log me in
						$r = Session::singleton()->login($user->email,$user->password,true);
						
						// go home
						if ( p('xhr') ) {
							$this->printJsonResponse(array( 'do' => 'redi', 'url' =>$cfg['after']));
						}
						else {
							$this->go( p('after', Config::url('home') ,$cfg) );
						}
						
					}
					else {
						$error = "There was an error processing your registration. Please try again later.";
					}
				
				}
				else {
					$error .= "<div>The email address {$user->email} is already registered.</div>";
					$noForm = true;
				}
			
			}
		
			// set error
			$args['error'] = $error;
			$args['form'] = $form;
		
		}
		
		// tolen
		$args['token'] = $this->generateFormToken('reg');
		$args['noForm'] = (!empty($noForm)?true:false);
		
		return Controller::renderTemplate(
			"reg/form.template.php",
			$args,
			BOLT_MODULES
		);
			
	
	}

}

?>