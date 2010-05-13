<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;
use \Config as Config;

class campaigns extends \FrontEnd {

	public function render($cfg=array()) {

		$c = $cfg['campaign'] = new \dao\campaign('get',array(p('id')));
		
		// sources
		$sources = array(
			'ya' => 'yahoo.com',
			'fa' => 'facebook.com',
			'go' => 'google.com',
		);
		
		// medium
		$mediums = array(
			'w' => 'web',
			'e' => 'email',
			'se' => 'sem',
			'so' => 'seo'
		);
		
		// campaigns
		$campaigns = array();
		
			// loop
			foreach ( new \dao\campaigns('get') as $item ) {
				$campaigns[$item->campaign] = $item->name;
			}
				        
		// define our form
        $cfg['form'] = array(
			"tags" => array(
			),
			'fields' => array(
								
				// info
				'name' => array('Name', array('name'=>'name', 'value' => $c->name, 'other' => array('id' => 'campaign-name') )),							
				'desc' => array('Description',array( 'name' => 'desc', 'type' => 'textarea', 'class' => 'big', 'parent' => 'meta', 'value' => $c->meta_desc )),
				
				'campaign' => array('Campaign', array('name'=>'campaign', 'type' => 'select', 'opts' => $campaigns, 'value' => $c->campaign, 'other' => array('id' => 'campaign-id') )),
				'source' => array('Source', array('name'=>'source', 'type' => 'select', 'opts' => $sources, 'value' => $c->source, 'other' => array('id' => 'campaign-source'), 'required' => false )),
				'medium' => array('Medium', array('name'=>'medium', 'type' => 'select', 'opts' => $mediums, 'value' => $c->medium, 'other' => array('id' => 'campaign-medium'), 'required' => false )),
				
				// content
				'content' => array('Content', array('name'=>'content', 'value' => $c->content, 'other' => array('id' => 'campaign-content') )),
				
				
			)
        );		
	
	
		switch( p('act',false,$cfg) ) {
		
			case 'dl':
				return $this->dl($cfg);

			case 'visitor':
				return $this->visitor($cfg);

			case 'view':
				return $this->view($cfg);
		
			case 'create':
				return $this->create($cfg);
		
			default:
				return $this->all($cfg);
		
		}
	
	}
	
	public function visitor($args) {
	
		$o = array(
			'campaign' => 'today',
			'view' => 'visitor',
			'keys' => p('id')
		);	
	
		// find all pv by this guid
		$v = $args['visits'] = new \dao\gertrude('get',array($o));
		
		// campaigns
		$campaigns = array();
		
		// add to campaigns
		foreach ( $v as $item ) {
			$campaings[$item->campaign->id][] = $item;
		}
		
		// add to args
		$args['campaings'] = $campaings;
	
		return Controller::renderModule(
			'campaigns/visitor',
			$args,
			BOLT_MODULES	
		);
		
	}
	
	public function view($args) {
		
		// campaign
		$c = $args['campaign'];
		
		// config
		$o = array( 	
			'campaign' => 'today', 
			'view' => 'campaign', 
			'keys' => $c->campaign,
			'filter' => array( 'source' => $c->source, 'medium' => $c->medium )
		);
		
		// list of last 100 visitors
		$args['today'] = new \dao\gertrude('get',array($o));		
		
		return Controller::renderModule(
			'campaigns/view',
			$args,
			BOLT_MODULES
		);	
	
	}
	
	public function create($args) {
	
		$args['error'] = false;
		
        // submit 
        if ( p('do') == 'submit' ) {
        
        	$d = $args['campaign'];
        
        	// validate
        	$f = Forms::validation($args['form']['fields']);
			
			// if errors
			if ( count($f['errors']) > 0 ) {
				foreach ( $f['errors'] as $e ) {
					$args['error'] .= "<li>{$e}</li>";
				}
			}

        	// errpr
        	if ( $args['error'] === false ) {
        	
        		// add eveything
        		foreach ( $args['form']['fields'] as $name => $field ) {
					if ( isset($field[1]['parent']) ) {
						$d->{"{$field[1]['parent']}_{$name}"} = $f['values'][$name];
					}
					else {
						$d->$name = $f['values'][$name];
					}
        		}
        	
				// new tags
				$d->tags = new \dao\tags();     			          	
        	
        		// tags
        		foreach ( $args['form']['tags'] as $name => $t ) {
        			
        			// p and v false
        			$p = $v = false;
        				
        				// what to set
        				if ( isset($t['predicate']) ) {
        					$p = $t['predicate'];
        					$v = $f['values'][$name];
        				}
        				else {
        					$p = $f['values'][$name];
        				}
        			  
        			// add a tag
        			$d->tags->add($t['namespace'],$p,$v);
        			
        		}	        			
				
				// added
				$d->added_ts = utctime();			
				
				// save 
				$d->save();
        		
        		// id
        		if ( $d->id ) {

					// no to the deal
					$this->go( Config::url('campaign-view',array('id'=>$d->id)) );

        		}
        		else {
        			$args['error'] = "Fatal Error! Please try again.";
        		}
        		
        	}
        
        }	
	
		return Controller::renderModule(
			'campaigns/create',
			$args,
			BOLT_MODULES
		);	
	
	}
	
	public function all($args) {
	
		$o = array('all'=>true);
	
		// make a list of campaigns
		$args['campaigns'] = new \dao\campaigns('get',array($o));
		
		// config
		$c = array( 'campaign' => 'today' );
		
		// list of last 100 visitors
		$args['today'] = new \dao\gertrude('get',array($c));
		
		return Controller::renderModule(
			'campaigns/list',
			$args,
			BOLT_MODULES
		);
	
	}
	


}

?>