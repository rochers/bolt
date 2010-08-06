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
	
	public function post($ep,$data) {
	
		// since posts are pretty bad,
		// we do a PUT instead and generate 
		// a uuid ourself. if you really need 
		// to do a post _post
		return $this->put($ep,$data);	
	
	}
	
	public function put($ep,$data,$noId=false) {
		
		// do they already have an id
		if ( $noId === false ) {
		
			// get uuid	
			$id = FrontEnd::getUuid();
	
			// add to ep
			$ep = rtrim($ep,'/').'/'.$id;
			
		}
	
		// end put
		$resp = $this->sendRequest($ep,json_encode($data),"PUT");
		
		// what up 
		if ( isset($resp['ok']) ) {
		
			// update the rev
			$this->data['_rev'] = $this->data['rev'] = $resp['rev'];
			$this->data['_id'] = $this->data['id'] = $resp['id'];
			
			// fire off an event
			$this->event->fire("dao-save",array(
				'data' => $this->normalize(),
				'endpoint' => $ep ,
				'dao' => get_class($this)
			));
			
			// yep
			return true;
			
		}
		else {
			return false;
		}
		
	}
	
	public function _post($ep,$data) {
		return $this->request($ep,$data,'POST');
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