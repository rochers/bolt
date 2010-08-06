<?php

abstract class DaoMongo extends Dao {

	// dbh
	protected $dbh = false;
	
	// privte info
	private $_host = false;
	private $_port = false;
	private $_db = false;

	// construct
	public function __construct($type=false,$args=array()) {
	
		// get some
		$this->_host = Config::get('site/mongo-host');
		$this->_port = Config::get('site/mongo-port');
		$this->_db = Config::get('site/mongo-db');
		
		// parent
		parent::__construct($type,$args);
	
	}

	private function _connect() {
		
		// already connected
		if ( $this->dbh ) { return; }
		
		// try to connect
		try { 
		
			// set dbh
			$this->dbh = new Mongo("mongodb://{$this->_host}:{$this->_port}");
			
		}
		catch ( MongoConnectionException $e ) { die( $e->getMessage() ); }
		
	}
	
	public function setDb($name) {
		$this->_db = $name;
	}
	
	public function query($collection,$query,$args=array()) {
		
		// try connecting
		$this->_connect();
		
		// sth
		$db = $this->dbh->{$this->_db};
		
		// sth
		$col = $db->{$collection};
	
		// find
		$sth = $col->find($query);
	
		// fields
		if ( isset($args['fields']) ) {
			$sth->fields($args['fields']);
		}
	
		// limit
		if ( isset($args['per']) ) {
			$sth->limit($args['per']);
		}
	
		// skip
		if( isset($args['start']) ) {
			$sth->skip($args['skip']);
		}
		
		// sort
		if ( isset($args['sort']) ) {
			$sth->sort($args['sort']);
		}
	
		// resp
		$resp = array();
		
		// get them
		while ( $sth->hasNext() ) {
			$resp[] = $sth->getNext();
		}
	
		// return a response
		return new DaoMongoResponse($resp,$sth);
	
	}
	
	public function count($collection,$query=array()) {
		
		// do it 
		$this->_connect();
		
		// sth
		$db = $this->dbh->{$this->_db};
		
		// sth
		$sth = $db->{$collection};		
			
		// reutrn it
		return $sth->count($query);
		
	}
	
	public function row($collection,$query,$args=array()) {
		
		// try connecting
		$this->_connect();		
		
		// send to query
		$args['per'] = 1;
		
		// get them
		$resp = $this->query($collection,$query,$args);
	
		// return the first one
		return $resp->item('first');
	
	}
	
	public function insert($collection,$data,$safe=false) {
	
		// try connecting
		$this->_connect();	
	
		// sth
		$db = $this->dbh->{$this->_db};
		
		// sth
		$sth = $db->{$collection};		
	
		// insert
		return $sth->insert($data,$safe);
	
	} 
	
	public function update($collection,$query,$data,$opts=array()) {
	
		// try connecting
		$this->_connect();	
	
		// sth
		$db = $this->dbh->{$this->_db};
		
		// sth
		$sth = $db->{$collection};			
	
		// run it
		return $sth->update($query,$data,$opts);
	
	}
	
	public function delete($collection,$query,$opts=array()) {
	
		// try connecting
		$this->_connect();	
	
		// sth
		$db = $this->dbh->{$this->_db};
		
		// sth
		$sth = $db->{$collection};			
	
		// run it
		return $sth->remove($query,$opts);	
	
	}
	
}

class DaoMongoResponse extends Dao implements Iterator {

	public function __construct($items,$cur) {
	
		// set items
		$this->items = $items;
		
		// set pager
		$this->setPager($cur->count(),1,1);
		
	}

}

?>