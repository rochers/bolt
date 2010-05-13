<?php

namespace dao;

class geocode extends Webservice {

	protected $data = array(
		'type' => false,
		'f_address'=> false,
		'address_components' => false,
		'city' => false,
		'country' => false,
		'lat'	=> false,
		'lng' => false
	);
	
	public function __construct() {
	
		parent::__construct(array('host'=>'maps.google.com'));
	
	}
	
	public function get($address) {
		
		$result = json_decode($this->sendRequest('maps/api/geocode/json?address='.urlencode($address).'&sensor=false'));
		
		$result = $result->results[0];
						
		$row = array(
			'type'=>$result->types[0],
			'f_address'=>$result->formatted_address,
			'city'=>$this->parseCity($result),
			'state'=>$this->parseState($result),
			'country'=>$this->parseCountry($result),
			'lat'=>$this->parseLat($result),
			'lng'=>$this->parseLon($result)
		);
					
		// set
		$this->set($row);
	
	}
	
	public function set($row) {
	
		$this->data = $row;
			
	}
	
    private function parseCity($result) {
	
		foreach ($result->address_components as $c) {
		
			if ($c->types[0] == 'locality') { 
				
				return $c->long_name;
			
			}
			
		}
		
	}
	
	
	private function parseState($result) {
	
		foreach ($result->address_components as $c) {
		
			if ($c->types[0] == 'administrative_area_level_1') { 
				
				return $c->short_name;
			
			}
			
		}
		
	}
	
	
	private function parseCountry($result) {
	
		foreach ($result->address_components as $c) {
		
			if ($c->types[0] == 'country') { 
				
				return $c->short_name;
			
			}
			
		}
		
	}
	
	
	private function parseLat($result) {
	
		return $result->geometry->location->lat;
		
	}
	
	private function parseLon($result) {
	
		return $result->geometry->location->lng;
		
	}
	
	
}


?>