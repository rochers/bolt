<?php

abstract class DaoWebservice extends Dao {
	
	protected $_ws = false;
	protected $_host = false;
	protected $_port = false;

	public function __construct($type=false,$args=array()) {
		
		// ws
		$this->_ws = new Webservice();
	
		// parent
		parent::__construct($type,$args);	
		
	}
		
	// send request
	public function sendRequest($ep,$params=array(),$method='GET',$headers=array()) {
		
		// set in ws
		$this->_ws->setHost($this->_host);
		$this->_ws->setPort($this->_port);

		// path
		return $this->_ws->sendRequest(
			$ep,
			$params,
			$method,
			$headers
		);

	}
		
}

?>