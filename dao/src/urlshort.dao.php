<?php

namespace dao;

class urlshort extends Webservice {
	
	private $bitlyLogin = 'dailyd';
	private $bitlyKey = 'R_7c34e7595991b7722a9fec9d8611a8d8';
	
	protected $data = array(
		'long'=>false,
		'short'=>false
	);
	
	public function __construct() {
				
		parent::__construct(array('host'=>'api.bit.ly'));
	
	}
	
	public function get($address) {
		
		// see if the url has been cached
		if ($cached = $this->cache->get($address,'urlshort')) { 
				
			$row = $cached; 
		
		// not cached so just do the webservice call
		} else { 
		
			$result = json_decode($this->sendRequest('v3/shorten?login='.$this->bitlyLogin.'&apiKey='.$this->bitlyKey.'&uri='.urlencode($address).'&format=json'));
					
			if ($result->status_code == '200') { 
				$short = $result->data->url;
			} else { 
				$short = $address;
			}
											
			$row = array(
				'long'=>$address,
				'short'=>$short,
			);
			
			// expire
			$expire = time()+(60*60*3);
		
			// add their session to the cache
			$this->cache->set($address,$row,$expire,'urlshort');
			
		}
							
		// set
		$this->set($row);
	
	}
	
	public function set($row) {
	
		$this->data = $row;
			
	}
		
	
}


?>