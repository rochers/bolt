<?php

namespace dao;

class campaigns extends \DaoDb implements \Iterator {

	// get a list
	public function get($cfg=array()) { 
		
		if ( isset($cfg['all']) ) {
			$sql = " SELECT c.id FROM campaigns as c  ";	
			$p = array();		
		}
		else {
			$sql = " SELECT DISTINCT c.id FROM campaigns as c WHERE c.source = '' AND c.medium = '' ";	
			$p = array();
		}
	
		// sth
		$sth = $this->query($sql,$p);
	
		// add
		foreach ( $sth as $row ) {
			$this->items[] = new campaign('get',array($row['id']));
		}
	
	}
	
}

?>