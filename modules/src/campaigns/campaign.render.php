<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class comments extends \FrontEnd {

	public function render($cfg=array()) {
	
		switch( p('act',false,$cfg) ) {
		
			case 'dl':
				return $this->dl($cfg);
		
			case 'create':
				return $this->create($cfg);
		
			default:
				return $this->all($cfg);
		
		}
	
	}
	
	public function all($args) {
	
		return Controller::renderModule(
		
		)
	
	}
	

}

?>