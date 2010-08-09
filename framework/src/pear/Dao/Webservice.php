<?php

namespace Dao;

/////////////////////////////////////////////////
/// @brief parent class for all database
///        database dao objects
/////////////////////////////////////////////////
abstract class Webservice extends \Dao {

	// info
	protected $host = false;	
	protected $port = false;
	
	// ws
	private $ws = false;

    /////////////////////////////////////////////////
    /// @brief construct a dao db  class
    ///
    /// @param type type of action to take after construct
    /// @param cfg configure options to pass to action after
    ///        construction
    /////////////////////////////////////////////////
	public function __construct($type=false,$cfg=array()) {

		// db
		$this->ws = new \Webservice(array(
			'host' => $this->host,
			'port' => $this->port
		));	

		// parent
		parent::__construct($type,$cfg);

	}	


    /////////////////////////////////////////////////
    /// @brief passthrough all calss to WS
    ///
    /// @param $fn function name
    /// @param $args arguments
    /////////////////////////////////////////////////    
	public function sendRequest($ep, $params) {
		return $this->ws->sendRequest($ep, $params);
	}


    /////////////////////////////////////////////////
    /// @brief passthrough to set
    ///
    /// @param $name property name
    /// @param $val value 
    /////////////////////////////////////////////////    
    public function _set($name, $val) {
		return $this->ws->{$name} = $val;
    }


    /////////////////////////////////////////////////
    /// @brief passthrough to get
    ///
    /// @param $name property name
    /////////////////////////////////////////////////    
    public function _get($name) {
		return $this->ws->{$name};
    }

}

?>