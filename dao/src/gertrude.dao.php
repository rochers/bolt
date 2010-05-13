<?php

namespace dao;

class gertrude extends \DaoDb implements \Iterator {

	const GERTRUDE_API = "http://localhost:5984/";

	// get a list
	public function get($cfg=array()) { 
		
		// campaign
		$campaign = p('campaign','unknown',$cfg);
		
		// p
		$p = array('descending'=>'true','include_docs'=>'true');
		
		// what view
		switch( p('view',false,$cfg) ){
		
			// visitor
			case 'visitor':
				$path = '_design/all/_view/by_guid';
				$p['k'] = json_encode($cfg['keys']);
				break;
			
			case 'campaign':
				$path = '_design/all/_view/by_campaign';
				$p['key'] = json_encode($cfg['keys']);
				break;
				
			default:
				$path = '_design/all/_view/by_timestamp';
		};
		
		// just get all docs for uknow
		$resp = $this->_request($campaign,$path,$p);
	
			// no resp
			if ( !$resp ) {
				return;
			}
	
		// items
		$items = array();
		
		// each
		foreach ( $resp['rows'] as $row ) {
		
			$ok = true;
		
			// filter
			if ( isset($cfg['filter']) ) {
				foreach ( $cfg['filter'] as $k => $v ) {
				
					if ( $v AND !isset($row['doc'][$k]) OR (isset($row['doc'][$k]) AND $row['doc'][$k] != $v) ) {
						$ok = false;
					}
				}
			
			}
		
			// add?
			if ( $ok == true ) { 
				$items[] = new gertrudeItem('set',$row['doc']);
			}
			
		}
	
		// items
		$this->items = $items;
	
	}
	
	public function create($name) {
	
		// create our database
		$this->_request($name,false,false,"PUT");
	
	}
	
	private function _isarray($x) {
		if ( !is_array($x) ) { return array($x); };
		return $x;
	}
	
	private function _request($db,$path=false,$params=array(),$method='GET',$post=false) {
	
		// url
		$url = self::GERTRUDE_API . $db .'/'. $path . "?" . \http_build_query($params);
		
		// headers
		$headers = array(
			'Content-Type' => 'application/json'
		);
	
        // new curl request
        $ch = curl_init();

        // set some stuff
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		
        // add headers
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    
        // add params
        if ( $post ) {
	        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        }
        
        // make the request
        $result = curl_exec($ch);  
        
        
		// bad curl call
        if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {        	
        	error_log($url);
        	error_log($result);
        	return false;
        }    
        
        // close curl
        curl_close($ch);           

		// give back json
		return json_decode($result,true);
	
	}
	
	// compile session
	public static function compileSessions($items) {
		
		// pv
		$sess = array();
	
		// sort by pageview id
		foreach ( $items as $i ) {
			$sess[$i->sid][$i->ts] = $i;
		}
	
		// now sort each by ts
		foreach ( $sess as $k => $v ) {
			krsort($sess[$k]);
		}
	
		// return
		return $sess;
	
	}
	
}

class gertrudeItem extends \DaoDb {
	protected $data = array(
		'id' => false,
		'guid' => false,
		'pvid' => false,
		'sid' => false,
		'ip' => false,
		'type' => false,
		'title' => false,
		'page' => false,
		'referrer' => false,
		'ts' => false,
		'campaign' => false,
		'page' => false,
		'page_id' => false,
		'source' => false,
		'medium' => false
	);
	protected $schema = array(
		'ts' => array('type'=>'timestamp'),
	);
	public function set($row) {
	
		// set 
		parent::set($row);
		
		$s = p('source',false,$row);
		$m = p('medium',false,$row);
		
		$this->data['campaign'] = new \dao\campaign('get',array($row['campaign'],$s,$m));
		
		// get path for url
		$this->private['page_parsed'] = parse_url($row['page']);
		$this->private['referrer_parsed'] = parse_url($row['referrer']); 
		
	}
}

?>