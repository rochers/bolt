<?php

namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class likebutton extends \FrontEnd {

	public function render($args=array()) {
		
		if (empty($args['asset'])) { 
			return '<!-- Like Button Module Error: You must specify an asset id for liking '.$args['asset'].' -->';
		} else { 
			$args['likes'] = new \dao\Likes('get',array(array('asset'=>$args['asset'],'user'=>$args['user'])));
		}
		
		
			$userLiked->user = false;
			
			// check if they already like this
			if (Session::getLogged()) { 
				
				$userLiked = new \dao\Like();
				$userLiked->asset = $args['asset'];
				$userLiked->user = Session::singleton()->uid;
				$userLiked->get();
				
			}
			
			//pass in a variable to switch the template view based on their selection
			if ($userLiked->user) { 
				$args['yourLike'] = true;
			} else { 
				$args['yourLike'] = false;
			}
		
		
		if ($args['type'] == 'text') { 
						
			// controller	
			return Controller::renderTemplate(
				"likebutton/text.template.php",
				$args,
				BOLT_MODULES
			);
		
		} else if ($args['type'] == 'tab') { 
						
			// controller	
			return Controller::renderTemplate(
				"likebutton/tab.template.php",
				$args,
				BOLT_MODULES
			); 		
		
		} else { 
			
			// controller	
			return Controller::renderTemplate(
				"likebutton/button.template.php",
				$args,
				BOLT_MODULES
			);
		
		}
			
	}
	
	public function ajax() { 
		
		//add new comment
		if (p('do') == 'like') { 
			
			$like = new \dao\Like();
			$like->asset = p('asset');
			$like->user = Session::singleton()->uid;
			
			if ($like->user > 0) { 
						
				// add it to the db
				$like->save();
				
				$this->printJsonResponse( array() );
			
			} else { 
				
				$this->printJsonResponse( array('errormsg'=>'Sorry, you must be signed in to "like" something.') );
			
			}
			
			
			
		}
			
	
	}
	
}


?>