<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class share extends \FrontEnd {

	public function render($args=array()) { 
		
		Controller::addEmbed('css','share-css','bolt','share.css');
		
		// defaults
		if (empty($args['url'])) { 
			$args['url'] = SELF;
		} 
		
		if (p('emailed',false)) { 
			$args['emailed'] = true;
		} 
		
		if (empty($args['title'])) { 
			$args['title'] = '';
		}
		
		if (empty($args['type'])) { 
			$args['type'] = 'bar';
		}
		
		if (empty($args['image'])) { 
			$args['image'] = 'https://www.dailyd.com/assets/customer/images/logo.png';
		}
		
		
		// shorten the url
		$short =  new \dao\urlshort();
		$short->get($args['url']);
		
		$args['shortUrl'] = $short->short;
		
		// twitter status
		$args['twitterUrl'] = "http://twitter.com/home?status=".rawurlencode(str_replace('%URL%',$args['shortUrl'],$args['twitter']));			
		
		$args['twitter'] = str_replace('%URL%',$args['shortUrl'],$args['twitter']);
		
		// email mailto link
		$args['email'] = str_replace('%URL%',$args['url'],$args['email']);
		
		
		// select template
		switch($args['type']) { 
		
			case 'expanded':
				$template = 'share/expanded';
				break;
			
			default: 
				$template = 'share/bar';
		
		}
		
		// check for sending emails
		if (p('do') == 'bolt-share-send-emails') { 
			
			$emailed = false;
			
			for($i=1;$i<5;$i++) { 
				
				$e = trim(p('email'.$i,false,$_POST,FILTER_VALIDATE_EMAIL));
				
				if (!empty($e)) { 
					
					$emailed = true;
									
					// send emails to friends
					$mailArgs = array(	'from'=>'no-reply@dailyd.com',
		         						'to'=>p('email'.$i),
		         						'subject'=>'Suggested deal from '.p('name'),
		         						'message'=> $args['email']
		         					);          	
		         	
		         	$this->sendEmail($mailArgs);
	         	
	         	}
         	
         	}
         	
         	if ($emailed) { 
         		
				if (stripos(p('origin'),'emailed=') > 0) { 
					
					$parts = explode("?",p('origin'));
					
					$url = $parts[0].'?emailed=true';
				
				} else { 
					
					$url = p('origin')."?emailed=true";
				
				}
					     		
         		$this->go($url);
         	
         	}
		
		}	
		
		
		
							
		// controller	
		return Controller::renderTemplate(
			$template,
			$args,
			BOLT_MODULES
		);
	
	}
	
}


?>