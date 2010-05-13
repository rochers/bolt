<?php

namespace dao;

class campaign extends \DaoDb implements \Iterator {
	
	protected $data = array(
		'name' => false,
		'campaign' => false,
		'source' => false,
		'medium' => false,
		'term' => false,
		'content' => false,
		'tags' => false,
		'meta' => false,
		'site' => false,
		'added_ts' => false,
		'changelog' => false,
	);
	
	protected $schema = array(
		'tags' => array( 'type' => 'tags' ),
		'meta' => array( 'type' => 'json' ),
		'added_ts' => array( 'type' => 'timestamp' ),
		'changelog' => array( 'type' => 'json' )
	);

	// get a list
	public function get($campaign,$source=false,$medium=false) { 
	
		$sql = " SELECT c.* FROM campaigns as c WHERE ";
	
		$where = array();
		$p = array();
		
		if ( !$source AND !$medium ) {
			$where[] = "c.id = ? OR c.campaign = ? ";
			$p[] = $campaign;
			$p[] = $campaign;			
		}
		else {
			
			$where[] = " c.campaign = ? ";
			$p[] = $campaign;
		
			// source
			if ( $source ) {
				$where[] = " c.source = ? ";
				$p[] = $source;
			}
			
			if ( $medium ) {
				$where[] = " c.medium = ? ";
				$p[] = $medium;
			}
		
		}
		
		$sql .= implode(" AND ",$where);
	
		// get it 
		$row = $this->row($sql,$p);
	
			// no row
			if ( !$row ) {
				return false;
			}
	
		// set
		$this->set($row);
	
	}
	
	public function save() {
	
		if ( $this->id ) {
		
		
		}
		else {
			
			$d = $this->normalize();
			
			$sql = "
				INSERT INTO 
					campaigns
				SET 
					name = ?,
					campaign = ?,
					source = ?,
					medium = ?,
					content = ?,
					tags = ?,
					meta = ?,
					added_ts = ?,
					changelog = ?
			";
		
			// do it 
			$this->data['id'] = $this->query($sql,array(
				$d['name'],
				$d['campaign'],
				$d['source'],
				$d['medium'],
				$d['content'],
				$d['tags'],
				$d['meta'],
				$d['added_ts'],
				$d['changelog']
			));
		
		}
	
	
	}
	
}

?>