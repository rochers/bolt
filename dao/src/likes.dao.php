<?php

namespace dao;

class likes extends \DaoDb implements \Iterator {

	// get a list
	public function get($cfg=array()) {
				
		// page
		$page = p('page',1,(array)$cfg);
		$per = p('per',20,(array)$cfg);
		$start = ($page-1)*$per;
			
		// where limits
		$where = array();
		$p = array();
	
		if ( isset($cfg['asset']) ) {
			$where[] = " l.deal = ? ";
			$p[] = $cfg['asset'];
		}
		
		if ( isset($cfg['user']) ) {
			$where[] = " l.user = ? ";
			$p[] = $cfg['user'];		
		}
		
	
		// sql
		$sql = "
			SELECT * 
			FROM likes as l
			WHERE ".implode(' AND ',$where)." 
			ORDER BY liked_ts ".(p('order','ASC',$cfg))." 
			LIMIT $start,$per
		";	
		
		$total = true;
							
		// sth
		$sth = $this->query($sql,$p,$total);
		
		// loop
		foreach ( $sth as $row ) {
			$this->items[] = new like('set',$row);
		}
		
		$this->setPager($total,$page,$per);
			
	}

}







?>