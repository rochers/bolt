<?php

namespace dao;

class blob extends \DaoDb {

	protected $data = array(
		'key' => false,
		'version' => false,
		'text' => false,
		'modified' => false,
		'user' => false,
	);
	
	protected $schema = array(
		'modified' => array( 'type' => 'timestamp' ),
		'user' => array( 'type' => 'dao', 'class' => 'user' )
	);

	public function get($cfg=false) {
	
		// sql
		$sql = " SELECT b.* FROM blobs as b ";
		$p = array();
		
		// if cfg is a string assume we what just a key
		if ( is_string($cfg) ) {
			$sql .= " WHERE b.key = ? ORDER BY b.version DESC LIMIT 1 ";
			$p[] = $cfg;
		}
		
		// array
		if ( is_array($cfg) ) {
			
			$sql .= " WHERE ";
			
			if ( isset($cfg['key']) ) {
				$sql .= " b.key = ? ";
				$p[] = $cfg['key'];
			}
		
			if ( isset($cfg['version']) ) {
				$sql .= " AND b.version = ? ";
				$p[] = $cfg['version'];
			}
		
		}
	
		// return them
		$sth = $this->query($sql,$p);
		
			// sth
			if ( !$sth ) {
				return;
			} 
		
		// foreach
		$this->set($sth[0]);
	
	}
	
	public function set($row) { 
	
		parent::set($row);
		
		// if it's json, let's make that available
		if (json_decode($row['text'])) { 
			
			$this->private['json'] = json_decode($row['text']);
		
		}
			
	
	}
	
	public function tokenize($tokens) {
	
		$str = $this->text;
		
		foreach ( $tokens as $k => $v ) {
			$str = str_replace('{'.$k.'}',$v,$str);
		}
		
		return $str;
	
	}

}

?>