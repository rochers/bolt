<?php

namespace dao;

class payments extends \DaoDb implements \Iterator {

	public function get($cfg) {
	
		// pager stuff
		$page = p('page',1,$cfg,FILTER_VALIDATE_INT);
		$per = p('per',20,$cfg,FILTER_VALIDATE_INT);
		$start = ($page-1)*$per;
	
		$where = array();
		$p = array();
				
		$sql = "
			SELECT 
				p.id
			FROM
				payments as p
		";
			

			//id 			
			if ( isset($cfg['id']) ) {
				$where[] = " p.id LIKE ? ";
				$p[] = "%{$cfg['id']}%";
			}	
			
			// filter
			$f = array('user','publisher','status','trans_id');
			
				// do my filters
				foreach ( $f as $i ) {
					if ( isset($cfg[$i]) AND $cfg[$i] !== false ) {
						$where[] = " p.{$i} = ? ";
						$p[] = $cfg[$i];
					}
				}								
			
		
		if (!empty($where)) { 
			$sql .= "WHERE ".implode(' '.p('op','AND',$cfg).' ',$where);
		}
		
		$sql .= " ORDER BY id ASC "; 
		$sql .= " LIMIT {$start},{$per} ";
					
		$total = true;					
					
		// sth
		$sth = $this->query($sql,$p,$total);
		
		foreach ( $sth as $row ) {
			$this->items[] = new payment('get',array($row['id']));
		}
		
		// set pager
		$this->setPager($total,$page,$per);
	
	}

}

?>