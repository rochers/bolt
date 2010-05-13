<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class comments extends \FrontEnd {

	public function render($args=array()) { 
		
		Controller::addEmbed("css","comments-default","bolt","comments-default.css");
		Controller::addEmbed("js","bolt-comments","bolt","comments.js");
		
		if (empty($args['id'])) { 
			$args['id'] = uniqid();
		}
		
		if (empty($args['asset'])) { 
			$comments = false;
		} else { 
			$comments = new \dao\Comments('get',array(array('asset'=>$args['asset'],'status'=>'A')));
		}
				
		$replies = array();
		
		foreach ($comments as $c) {
			
			//go through each reply and store a reference to it
			if ($c->parent_comment) { 
				
				$replies[$c->parent_comment][] = $c;
			
			} 
		
		}
			
		
		// re sort the comments to be threaded	
		foreach ($comments as $c) { 
		
			if (!$c->parent_comment) { 
				
				$args['comments'][] = $c;
				
				if (isset($replies[$c->id])) { 
					
					foreach ($replies[$c->id] as $reply) { 
					
						$args['comments'][] = $reply;
					
					}
				
				}
				
			}
			
		} 
		
						
		// controller	
		return Controller::renderTemplate(
			"comments/comments",
			$args,
			BOLT_MODULES
		);
	
	}
	
	public function ajax() { 
		
		//add new comment
		if (p('do') == 'add') { 
			
			$comment = new \dao\Comment();
			$comment->comment = p('comment');
			$comment->user = Session::singleton()->uid;
			$comment->asset = p('asset');
			
			if ($comment->user > 0 && $comment->comment) { 
			
				// add it to the db
				$comment->save();
				
			}
			
		}
			
	
	}
	
}


?>