<?php

// bolt namespace
namespace bolt;

// session name
use \Session as Session;
use \Forms as Forms;
use \Controller as Controller;

class form extends \FrontEnd {

	public function render($cfg) {
		
		// generate form
		$cfg['form'] = self::generate($cfg,$this->args['parent'][$cfg['data']]);
		
		return Controller::renderModule(
			'form/form',
			$cfg,
			BOLT_MODULES
		);
	
	}
	
	public static function generate($cfg,$data) {
		
		// ignore
		$ignore = p('ignore',array(),$cfg);

		// form
		$fields = array(
		
		);
		
		// schema
		$schema = $data->getSchema();
		
		// loop through data and add it to the form
		foreach ( $data->getData() as $k => $v ) {
		
			// ignore
			if ( in_array($k,$ignore) ) {
				continue;
			}
			
			// does schema exist
			if ( isset($schema[$k]) ) {
			
				// typw
				switch( $schema[$k]['type'] ) {
					
					// json
					case 'json':
					
						// loop through the json and add each as a field
						if ( is_array($v) ) {
							foreach ( $v as $kk => $vv ) {
								$fields[$kk] = array( ucfirst($kk), array( 'name' => $kk, 'parent' => $k, 'value' => $vv ) );
							}
						}
							
						break;	
					
					// ts
					case 'timestamp':
						
						// timestamp
						$fields[$k] = array( ucfirst($k), array( 'name' => $k, 'type' => 'datetime', 'value' => $v ) );				
						
						break;		
				
					// mapped
					case 'mapped':
					
						// timestamp
						$fields[$k] = array( ucfirst($k), array( 'name' => $k, 'type' => 'select', 'opts' => $schema[$k]['map'], 'value' => $v ) );									
						
						break;
						
					case 'dao':
						
						$fields[$k] = array( ucfirst($k), array( 'name' => $k, 'value' => $v->id ) );					
						break;
					

					default: 
						$fields[$k] = array( ucfirst($k), array( 'name' => $k, 'value' => $v ) );					
					
				}
			
			}
			else {			
				$fields[$k] = array( ucfirst($k), array( 'name' => $k, 'value' => $v ) );
			}
			
		}
	
		// see if there's anything to override
		if ( isset($cfg['override']) ) {
			foreach ( $cfg['override'] as $k => $opts ) {
				if ( isset($fields[$k]) ) {
					foreach ( $opts as $_k => $_v ) {
						$fields[$k][1][$_k] = $_v;
					}
				}
			}
		}
	
		// form
		return array(
			'fields' => $fields
		);	
	
	}
	
}

?>