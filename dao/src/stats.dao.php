<?php

namespace dao;

class stats extends \DaoDb {

	public function get($cfg) {
		
		// sql
		$sql = " SELECT * FROM stats as s ";	
		$where = array();
		$p = array();
	
		// asset and type
		if ( isset($cfg['id']) ) {
			$where[] = " s.asset_id = ? ";
			$p[] = $cfg['id'];
		}
	
		if ( isset($cfg['type']) ) {
			$where[] = " s.asset_type = ? ";
			$p[] = $cfg['type'];		
		}
	
		// tags?					
		if ( isset($cfg['tags']) ) {
			
			// make an array
			if ( !is_array($cfg['tags']) ) {
				$cfg['tags'] = array($cfg['tags']);
			}
			
			// where
			foreach ( $cfg['tags'] as $tg ) {
				$where[] = "FIND_IN_SET(?,s.tags) ";
				$p[] = $tg;
			}
		}		
		
		// run it 
		
		
	}

}


?>