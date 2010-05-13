<?php

namespace dao;

class comments extends \DaoDb implements \Iterator {

	// get a list
	public function get($cfg=array()) { 
				
		// page
		$page = p('page',1,(array)$cfg);
		$per = p('per',20,(array)$cfg);
		$start = ($page-1)*$per;
			
		// where limits
		$where = array();
		$p = array();
	
		// status
		if ( isset($cfg['asset']) ) {
			$where[] = " c.asset = ? ";
			$p[] = $cfg['asset'];
		}
		
		if ( isset($cfg['status'])) { 
			$where[] = " c.status = ? ";
			$p[] = $cfg['status'];
		}
	
		// sql
		$sql = "
			SELECT * 
			FROM comments as c
			WHERE ".implode(' AND ',$where)." 
			ORDER BY id ASC 
			LIMIT $start,$per
		";	
	
		$total = true;
							
		// sth
		$sth = $this->query($sql,$p,$total);
		
		// loop
		foreach ( $sth as $row ) {
			$this->items[] = new comment('set',$row);
		}
			
		$this->setPager($total,$page,$per);
			
	}

}







?>