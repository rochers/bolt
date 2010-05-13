<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;

class account extends \FrontEnd {

	public function __construct($args) {
		parent::__construct();
		$this->args = $args;
	}
	
	public function render($cfg=array()) {
	
		// register our arg
		Forms::$arg = "acct";
		
		$user = Session::getUser();	
		
		$tz = array(
			'-12'	=> '[GMT-12] Eniwetok, Kwaialein',
			'-11'	=> '[GMT-11] Midway Island, Samoa',
			'-10'	=> '[GMT-10] Hawaii, Honolulu',
			'-9'	=> '[GMT-9] Alaska',
			'-8'	=> '[GMT-8] Anchorage, Los Angeles, San Francisco, Seattle',
			'-7'	=> '[GMT-7] Denver, Edmonton, Phoenix, Salt Lake City, Santa Fe',
			'-6'	=> '[GMT-6] Chicago, Guatamala, Mexico City, Saskatchewan East',
			'-5'	=> '[GMT-5] Bogota, Kingston, Lima, New York',
			'-4'	=> '[GMT-4] Caracas, Labrador, La Paz, Maritimes, Santiago',
			'-3.5'	=> '[GMT-3.5] Standard Time [Canada], Newfoundland',
			'-3'	=> '[GMT-3] Brazilia, Buenos Aires, Georgetown, Rio de Janero',
			'-2'	=> '[GMT-2] Mid-Atlantic',
			'-1'	=> '[GMT-1] Azores, Cape Verde Is.',
			'0'		=> '[GMT] Dublin, Edinburgh, Iceland, Lisbon, London, Casablanca',
			'1'		=> '[GMT+1] Amsterdam, Berlin, Bern, Brussells, Madrid, Paris, Rome, Oslo, Vienna',
			'2'		=> '[GMT+2] Athens, Bucharest, Harare, Helsinki, Israel, Istanbul',
			'3'		=> '[GMT+3] Ankara, Baghdad, Bahrain, Beruit, Kuwait, Moscow, Nairobi, Riyadh',
			'3.5'	=> '[GMT+3.5] Iran',
			'4'		=> '[GMT+4] Abu Dhabi, Kabul, Muscat, Tbilisi, Volgograd',
			'4.5'	=> '[GMT+4.5] Afghanistan',
			'5'		=> '[GMT+5] Calcutta, Madras, New Dehli',
			'5.5'	=> '[GMT+5.5] India',
			'6'		=> '[GMT+6] Almaty, Dhakar, Kathmandu',
			'6.5'	=> '[GMT+6.5] Rangoon',
			'7'		=> '[GMT+7] Bangkok, Hanoi, Jakarta, Phnom Penh',
			'8'		=> '[GMT+8] Beijing, Hong Kong, Kuala Lumpar, Manila, Perth, Singapore, Taipei',
			'9'		=> '[GMT+9] Osaka, Sapporo, Seoul, Tokyo, Yakutsk',
			'9.5'	=> '[GMT+9.5] Adelaide, Darwin',
			'10'	=> '[GMT+10) Brisbane, Canberra, Guam, Hobart, Melbourne, Port Moresby, Sydney',
			'11'	=> '[GMT+11] Magadan, New Caledonia, Solomon Is.',
			'12'	=> '[GMT+12] Auckland, Fiji, Kamchatka, Marshall Is., Suva, Wellington',
			'dst'	=> '[DST]'
		);
		
		// our form to start
		$form = array(
			'name' => array("Name",array('name'=>'name',"required"=>true, 'value' => $user->name)),
			'email' => array("Email Address",array('name'=>'email',"required"=>true, 'value' => $user->email)),
			'password' => array("Password",array('name'=>'pword',"type"=>"password","required"=>false)),
			'tzoffset' => array("Timezone",array('name'=>'tzoffset','type'=>'select','opts'=>$tz, 'value' => ($user->profile_tzoffset/(60*60)) ))
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
			'error' => false
		);
		
		// submit
		if ( p('do') == 'acct.submit' ) {
		
			// error
			$error = false;
			
        	// validate
        	$f = Forms::validation($form);			
			
			// if errors
			if ( count($f['errors']) > 0 ) {
				foreach ( $f['errors'] as $e ) {
					$error .= "<div>{$e}</div>";
				}
			}			
					
			// if 
			if ( !$this->validateFormToken('acct',p('acct_token') ) ) {
				$error .= "<div>Invalid Form Token</div>";
			}
			
			// form
			$f = $f['values'];
			
			// user
			$user = new \dao\user('get',array( Session::getUser()->id ));			
			
			// make sure they didn't change to another email
			if ( $f['email'] != $user->email ) {

				// check if there's a user with this email address
				$check = new \dao\user('get',array($f['email']));
				
				// check
				if ( $check->id ) {
					$error .= "<div>The Email Adress {$user->email} is already registered</div>";				
				}
			
			}
			
			// no erro
			if ( $error === false ) { 
	
				// name
				$name = explode(' ',$f['name']);
				$pw = $user->password;
			
				// make their password
				if ( $f['password'] ) { 
					 $pw = \dao\user::encrypt($f['password']);
				}
								
				// tzoffset
				$f['tzoffset'] *= (60*60);
				$tz = false;
				
				foreach ( \DateTimeZone::listAbbreviations() as $zone ) {
					foreach ( $zone as $ab ) {													
						if ( $f['tzoffset'] == $ab['offset'] AND $ab['timezone_id'] ) {
							$f['tz'] = $ab['timezone_id']; break;
						}
					}
				}
					
				$user->password = $pw;	
				$user->set(array(
					'password'	=> $pw,
					'email' 	=> $f['email'],
					'firstname' => array_shift($name),
					'lastname'	=> implode(' ',$name)
				));
				
				// everything else goes into profile
				unset($f['password'],$f['email'],$f['name']);
				
				// each 
				foreach ( $f as $key => $val ) {
					$user->{"profile_$key"} = $val;
				}
		
				// any tags
				if ( isset($cfg['tags']) )	{
					
					$user->tags->removeAll();
					
					foreach ( $cfg['tags'] as $fid => $group ) {
						
						$user->tags->add( $group,$f[$fid] );
					
					}
				}
				
				// save me 
				$resp = $user->save();
			
				// what 
				if ( $resp ) {
					
					// log them out 
					$s = Session::singleton();
					
					// logout
					$s->logout();
					
					// log me in
					$r = $s->login($user->email,$user->password,true);
					
					// go home
					if ( p('xhr') ) {
						$this->printJsonResponse(array( 'do' => 'redi', 'url' =>$cfg['after']));
					}
					else {
						$this->go($cfg['after']);
					}
					
				}
				else {
					$error = "Fatal Error!";
				}
		
			
			}
		
			// set error
			$args['error'] = $error;
			$args['form'] = $form;
		
		}
		
		// tolen
		$args['token'] = $this->generateFormToken('acct');

		
		return Controller::renderTemplate(
			"account/form.template.php",
			$args,
			BOLT_MODULES
		);
	
	}

}

?>