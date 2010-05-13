<?php

namespace dao;

class purchase extends \DaoDb {

	private $types = array(
		'D' => array( 'class' => 'deal' )
	);

	public static $statuses = array(
		'' => 'Open',
		'P' => 'Open',
		'U' => 'Customer Marked Used',
		'M' => 'Merchant Marked Used',
		'C' => 'Canceled'
	);

	protected $data = array(
		'user' => false,
		'payment' => false,
		'asset_id' => false,
		'asset_type' => false,
		'publisher'	=> false,
		'merchant' => false,
		'changelog' => false,
		'added_ts' => false,
		'modified_ts' => false,
		'status' => 'P'
	);

	protected $schema = array(
		'user' => array( 'type' => 'dao', 'class' => 'user' ),
		'payment' => array( 'type' => 'dao', 'class' => 'payment' ),
		'publisher'	=> array( 'type' => 'dao', 'class' => 'publisher' ),
		'merchant' => array( 'type' => 'dao', 'class' => 'merchant' ),
		'changelog' => array( 'type' => 'json' )
	);
	
	public function get($id) {
		
		// try to get the ourchase
		$row = $this->row("SELECT * FROM purchases as p WHERE p.id = ? ",array($id));
		
			// if no we stop
			if ( !$row ) {
				return;
			}
	
		// set
		$this->set($row);
	
	}
	
	public function set($row) {
	
		// set to parent first
		parent::set($row);	
		
		// id
		$this->id = strtoupper($this->id);
		
		// class
		$a = '\\dao\\' . $this->types[$this->asset_type]['class'];
	
		// now switch based on asset
		$this->private['asset'] = new $a('get',array( $this->asset_id ));
		
		// mod
		$this->private['f_added'] = ago($this->added_ts);
		$this->private['f_modified'] = ago($this->modified_ts);
		
		// status
		$this->private['f_status'] = self::$statuses[$this->status];
	
	}
	
	public function save() {
	
		// normalize
		$data = $this->normalize();		
		
		// update 
		if ( $this->id !== false ) {
		
			// sql
			$sql = "
				UPDATE `purchases` SET 
					`payment` = ?,
					`user` = ?,
					`asset_id` = ?,
					`asset_type` = ?,
					`publisher` = ?,
					`merchant` = ?,
					`changelog` = ?,
					`modified_ts` = ?,
					`status` = ?
				WHERE `id` = ?		
			";
			
			// run it 
			$r = $this->query($sql,array(
				$data['payment'],
				$data['user'],
				$data['asset_id'],
				$data['asset_type'],
				$data['publisher'],
				$data['merchant'],
				$data['changelog'],
				utctime(),			
				$data['status'],
				$this->id
			));		
		
			// nope
			if ( $r == false ) {
				return false;
			}
		
		}
		else {
		
			// get the id from the combining the payment and user
			$id = $data['user'] . "-" . uniqid();
			
			// no stauts
			if ( !$data['status'] ) {
				$data['status'] = 'P';
			}
		
			// sql
			$sql = "
				INSERT INTO `purchases` SET 
					`id` = ?,
					`payment` = ?,
					`user` = ?,					
					`asset_id` = ?,
					`asset_type` = ?,
					`publisher` = ?,
					`merchant` = ?,					
					`changelog` = ?,
					`added_ts` = ?,
					`modified_ts` = ?,
					`status` = ?
			";
			
			// run it 
			$r = $this->query($sql,array(
				$id,
				$data['payment'],
				$data['user'],				
				$data['asset_id'],
				$data['asset_type'],
				$data['publisher'],
				$data['merchant'],				
				$data['changelog'],
				utctime(),
				utctime(),
				$data['status']
			));
			
			// if it worked
			if ( $r !== false ) {
				$this->data['id'] = $id;
				return true;
			}
		
		}
	
	
	}
	
}


?>