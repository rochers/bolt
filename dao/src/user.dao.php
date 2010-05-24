<?php

namespace dao;

class user extends \DaoDb {

	protected $trackChanges = true;

	protected $data = array(
		'username' => false,
		'password' => false,
		'firstname' => false,
		'lastname' => false,
		'email' => false,
		'tags' => false,
		'profile' => false,
		'changelog' => false	
	);
	
	protected $schema = array(
	   'password' => array( 'private' => true ),
	   'profile' => array( 'type' => 'json' ),
	   'tags' => array( 'type' => 'tags' ),
	   'changelog' => array( 'type' => 'json' )
	);

	public function get($by,$other=array()) {
	
		// by is zero
		if ( !$by ) {
			return;
		}
	
		$sql = "SELECT u.* FROM users as u";
		$where = array();
		$p = array();
			
		// tags
		if ( isset($other['tags']) ) {
			foreach ( $other['tags'] as $tag ) {
				if ( trim($tag) ) {
					$where[] = "FIND_IN_SET(?,u.tags)";
					$p[] = $tag;
				}
			}
		}
		
		if ( $by != false ) {
		
			// the default 
			$where[] = " u.email = ? ";
			$where[] = " u.id = ? ";
			$where[] = " u.username = ? ";			
			$p[] = $by;
			$p[] = $by;
			$p[] = $by;
						
		}	

			
		// sql
		$sql .= ' WHERE ' . implode(" OR ",$where);		
	
		// get it
		$row = $this->row($sql,$p);
		
			// what 
			if ( !$row ){ return false; }
	
		// set it 
		$this->set($row);
	
	}
	
	public function set($row) { 
	
        parent::set($row);
	
		// set some stuff
		$this->data['name'] = trim( implode(" ",array($row['firstname'],$row['lastname'])) );
			
		// nick
		$this->private['nick'] = trim($this->data['firstname'] . ' ' . substr($this->data['lastname'],0,1));
			
		// if no name we want email
		if ( empty($this->data['name']) ) {
			$this->name = $this->email;
			$e = explode("@",$this->email);
			$this->private['nick'] = $e[0];
		}
						
	}
	
	public function save() {		
		
		// sql
		$sql = "";
		$p = array();
				
		// insert or update
		if ( $this->id ) {
						
			/* gather our changelog
			$data['changelog'][utctime()] = array(
				'text' => '',
				'changes' => $this->changes,
				'name' => \Session::getUser()->name,
				'user' => \Session::getUser()->id
			); */
			
			// normailze
			$data = $this->normalize();							
					
			$sql = "
				UPDATE
					users 
				SET 
					username = ?,
					password = ?,					
					firstname = ?,
					lastname = ?,
					email = ?,
					tags = ?,
					profile = ?,
					changelog = ?
				WHERE
					id = ?
			";
			
			$p = array(
				$data['username'],			
				$this->password,			
				$data['firstname'],
				$data['lastname'],
				$data['email'],
				$data['tags'],
				$data['profile'],
				$data['changelog'],
				$this->id
			);			
		
		}
		else {
			
			// normailze
			$data = $this->normalize(); 		
				
			$sql = "
				INSERT INTO
					users 
				SET 
					username = ?,
					password = ?,					
					firstname = ?,
					lastname = ?,
					email = ?,
					tags = ?,
					profile = ?,
					changelog = ?
			";
			
			$p = array(
				$data['username'],
				$this->password,			
				$data['firstname'],
				$data['lastname'],
				$data['email'],
				$data['tags'],
				$data['profile'],			
				$data['changelog'],				
			);		
			
		}
		
		// do it
		$this->data['id'] = $this->query($sql,$p);
		
		return $this->data['id'];
	
	}
		
	public static function encrypt($str) {
		return md5('asd90j83j2[@U*EJQ()_)das'.md5('9du8jwp2i9q3rk8d2[d0'.$str).'i9e203jd[q0k2-30dj9]-dk0q3,');
	}

}

?>