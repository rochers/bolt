<?php

namespace dao;

class like extends \DaoDb {

	protected $data = array( 	
			'asset' => false,
			'user' => false,
			'liked_ts' => false
		);


	// get
	public function get() {
		
		// get the deal
		$row = $this->row("SELECT * FROM likes as l WHERE l.deal = ? AND l.user = ? ",array($this->data['asset'],$this->data['user']));
		
		// set 
		$this->set($row);
	
	}
	
	public function set($row) {
	
		if ( empty($row) ) { 
			$this->data['asset'] = false;
			$this->data['user'] = false;
			$this->data['liked_ts'] = false;
		

		} else { 				
			$this->data['deal'] = new \dao\ddDeal('get',array($row['deal']));		
			$this->data['asset'] = $row['deal'];
			$this->data['user'] = $row['user'];
			$this->data['liked_ts'] = $row['liked_ts'];
		
		}
	
	}
	
	public function save() { 
		
		//check to make sure this is new
		$row = $this->row("SELECT * FROM likes as l WHERE l.deal = ? AND l.user = ? ",array($this->data['asset'],$this->data['user']));
								
		if (empty($row)) { 	
		
			// sql
			$sql = "";
			$p = array();
			
			// sql
			$sql = "
				UPDATE
					deals
				SET
					like_count = (like_count + 1)
				WHERE
					id = ?
			";
				
			// params
			$p = array(
				$this->data['asset']
			);		
				
			$this->query($sql,$p);				
		
			// add a row to the likes table for record keeping
			$sql = "";
			$p = array();
			
			// sql
			$sql = "
				INSERT INTO
					likes
				SET
					deal = ?,
					user = ?,
					liked_ts = ?
			";
				
			// params
			$p = array(
				$this->data['asset'],
				$this->data['user'],
				strtotime('now')
			);		
				
			// run it 
			return $this->query($sql,$p);
		
		} else { 
		
			return false;
		
		}
		
		
			
	}
	
	public function delete() {
		
		

		
	}

}

?>