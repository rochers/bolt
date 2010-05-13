<?php

namespace dao;

class purchases extends \DaoDb implements \Iterator  {

	// get some purchases
	public function get($cfg) {
	
		// page info
		$page = p('page',1,$cfg);
		$per = p('per',20,$cfg);
		$start = ( $page - 1 ) * $per;	
		
		// stuff
		$tags = p('tags',array(),$cfg);
		
		// sql
		$sql = " SELECT p.id FROM purchases as p ";
		$p = array();
		$where = array();
	
		// filter
		$f = array('user','merchant','publisher','status','payment','asset_id');
		
			// do my filters
			foreach ( $f as $i ) {
				if ( isset($cfg[$i]) AND $cfg[$i] !== false ) {
					$where[] = " p.{$i} = ? ";
					$p[] = $cfg[$i];
				}
			}


		// start	
		if ( p('start',false,$cfg) ) {
			$where[] = " p.added_ts >= ? ";
			$p[] = $cfg['start'];
		}

		if ( p('end',false,$cfg) ) {
			$where[] = " p.added_ts <= ? ";
			$p[] = $cfg['end'];
		}
		
		if ( p('id',false,$cfg) ) {
			$where[] = " p.id LIKE ? ";
			$p[] = "%{$cfg['id']}%";
		}	
	
		// wher
		if ( count($where) > 0 ) {
			$sql .= " WHERE " . implode(" AND ",$where);
		}
		
		// order
		if ( isset($cfg['order']) ) {
			$sql .= " ORDER BY p.{$cfg['order']} ";
			$sql .= (isset($cfg['desc'])?' DESC ': ' ASC ');
		}
	
		// limit 
		$sql .= " LIMIT {$start},{$per} ";
			
		// total
		$total = true;
		
		// run it
		$sth = $this->query($sql,$p,$total);
	
		// has tags
		$hasTags = count($tags);
	
		// make them
		foreach ( $sth as $row ) {
			
			// o 
			$p = new purchase('get',array($row['id']));
		
			// check it 
			if ( !$hasTags OR ( $hasTags AND $this->_tagCheck($tags,$p->asset->tags) ) ) {
				$this->items[] = $p;
			}
			else {
				$total--;
			}
		}
	
		// set page
		$this->setPager($total,$page,$per);
	
	}
	
	private function _tagCheck($find,$current) {
	
		// lets expand out our tags
		$tags = explode(',',$current);
		
		// how many did we find
		$f = 0;
		
		// loop through them
		foreach ( $find as $t ) {
			list($ns,$pred,$val) = tag::parse($t);			
			if ( $current->get($ns,$pred,$val)->total > 0 ) {
				$f++;
			}
		}
	
		// how many found
		return ($f==count($find));
	
	}

}

?>