<?php

namespace dao;

class comment extends \DaoDb {

	protected $data = array( 	
			'status' => 1,
			'id' => false,
			'comment' => false,
			'asset' => false,
			'user' => false,
			'role' => 'U',
			'created_ts' => false,
			'parent_comment' => false,
			'status' => 'A'
		);

	protected $schema = array(
		'user' => array( 'type' => 'dao', 'class' => 'user' )
	);

	// get
	public function get($id) {
	
		// get the deal
		$row = $this->row("SELECT * FROM comments as c WHERE c.id = ? ",array($id));
				
		// set 
		$this->set($row);
	
	}
	
	public function set($row) {
	
		if ( empty($row) ) return;		
	
		parent::set($row);
		
		
		if ($this->status == 'A') {
		
			$this->private['f_status'] = 'Live';
		
		}
			
		//$this->data = $row;
		
	}
	
	public function save() {
	
		if ($this->id) { 
			
			// sql
			$sql = "";
			$p = array();
			
			// sql
			$sql = "
				UPDATE
					comments 
				SET 
					comment = ?,
					status = ?
				WHERE id = ?
				LIMIT 1
			";
				
			// params
			$p = array(
				$this->data['comment'],
				$this->data['status'],
				$this->id				
			);
			
		
		} else { 
		
			// sql
			$sql = "";
			$p = array();
			
			// sql
			$sql = "
				INSERT INTO 
					comments 
				SET 
					comment = ?,
					asset = ?,
					user = ?,
					role = ?,
					parent_comment = ?,
					created_ts = ?,
					status = ?
			";
				
			// params
			$p = array(
				$this->data['comment'],
				$this->data['asset'],
				$this->data['user'],
				$this->data['role'],
				$this->data['parent_comment'],
				strtotime('now'),
				$this->data['status']				
			);	
			
		}	
			
	
		// run it 
		return $this->query($sql,$p);
	
	}
	
	public function delete() {
		
		// sql
		$sql = "
			DELETE FROM
				comments
			WHERE
				id = ?
			LIMIT 1
		";
			
		// params
		$p = array(
			$this->data['id']				
		);		
			
	
		// run it 
		return $this->query($sql,$p);

		
	}

}

?>