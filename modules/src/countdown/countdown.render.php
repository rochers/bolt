<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class countdown extends \FrontEnd {

	public function render($args=array()) {
		
		//Controller::addEmbed("css","comments-default","bolt","comments-default.css");
		//Controller::addEmbed("js","bolt-comments","bolt","comments.js");
		
		if (empty($args['id'])) { 
			$args['id'] = uniqid();
		}
		
		if (empty($args['endtime'])) { 
			$args['endtime'] = strtotime('now');
		}
		
		if (empty($args['timeleft'])) { 
			$args['timeleft'] = 'Time Left: ';
		}
		
		$args['left'] = left($args['endtime']);
						
		// controller	
		return Controller::renderTemplate(
			"countdown/countdown",
			$args,
			BOLT_MODULES
		);
	
	}
	
}


?>