<?php

abstract class DaoCouch extends DaoWebservice {

	// construct
	public function __construct($type=false,$args=array()) {
	
		// get some
		$this->_host = Config::get('site/couch-host');
		$this->_port = Config::get('site/couch-port');
		
		// parent
		parent::__construct($type,$args);
	
	}
	
	// single 
	public function row($ep,$data=array(),$method='GET') {
		
		// find
		$resp = $this->request($ep,$data,$method);
		
		// none
		if ( !$resp ){ return false; }
		
		// rows
		if ( !isset($resp['_id']) ) {
			return $resp[0];
		}
		else {
			return $resp;
		}
	
	}
	
	public function query($ep,$data=array(),$method='GET') {
		return $this->request($ep,$data,$method);
	}
	
	// request
	public function request($ep,$data=array(),$method='GET') {
	
		// key
		if ( $method == 'GET' ) {

			// limit 
			$data['limit'] = p('limit',20,$data);
			
			foreach ( $data as $k => $v ) {
				switch($k) {
					case 'key':
					case 'startkey':
					case 'endkey':
						$data[$k] = json_encode($v); break;					
				};
			}			
		
		}
		else if ( $method == 'POST' OR $method == 'PUT' ) {
			$data = json_encode($data);
		}
	
		// make it 
		$res = $this->sendRequest($ep,$data,$method);
	
		// if no resp we stop now
		if ( !$res ) {
			return false;
		}
	
		// return our data
		if ( isset($res['rows']) ) {
		
			// set pager
			$this->setPager($res['total_rows'],$res['offset'],$data['limit']);
		
			// items
			$items = array();
			
			foreach ( $res['rows'] as $row ) {
				$row['value']['id'] = $row['id'];
				$row['value']['rev'] = (isset($row['value']['rev'])?$row['value']['rev']:$row['value']['_rev']);
				$items[] = $row['value'];				
			}
		
			// rows
			return $items;
			
		}
		else {
		
			$res['id'] = $res['_id'];
			$res['rev'] = $res['_rev'];
		
			return $res;
		}
	
	}

}


?>