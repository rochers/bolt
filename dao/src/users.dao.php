<?php

namespace dao;

class users extends \DaoDb implements \Iterator {

	public function get($limit=array()) {
				
		$where = array();
		$p = array();
				
		$sql = "
			SELECT 
				u.*
			FROM
				users as u
		";
			
			if ( isset($limit['area']) ) {
				$where[] = "FIND_IN_SET('area:{$limit['area']}',u.tags) ";
			}
			
			if ( isset($limit['firstname']) ) {
				$where[] = " u.firstname LIKE ? ";
				$p[] = "%{$limit['firstname']}%";
			}

			if ( isset($limit['lastname']) ) {
				$where[] = " u.lastname LIKE ? ";
				$p[] = "%{$limit['lastname']}%";
			}
			
			if ( isset($limit['id']) ) {
				$where[] = " u.id = ? ";
				$p[] = $limit['id'];
			}			
			
		
		if (!empty($where)) { 
			$sql .= "WHERE ".implode(' '.p('op','AND',$limit).' ',$where);
		}
		
		$sql .= "ORDER BY id ASC"; 
					
		// sth
		$sth = $this->query($sql,$p);
	
		// items
		$items = array();
		
		foreach ( $sth as $row ) {
			$items[] = new user('set',$row);
		}
		
		// global
		$this->items = $items;
		
		$this->total = count($this->items);
	
	}

}

?>