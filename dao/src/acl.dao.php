<?php

namespace dao;

class acl extends \DaoDb implements \Iterator {


	public static $types = array();

    public function get($cfg=array()) {
    
        // page
        $page = p('page',1,$cfg);
        $per = p('per',50,$cfg);
        $start = ($page-1) * $per;
    
        // sql
        $sql = " SELECT * FROM acl as a ";
        $where = array();
        $p = array();
        
            // if type
            if ( isset($cfg['type']) ) {
                $where[] = " a.type = ? ";
                $p[] = $cfg['type'];
            }
            
            // user
            if ( isset($cfg['user']) ) {
                $where[] = " a.user = ? ";
                $p[] = $cfg['user'];
            }
        
            // id
            if ( isset($cfg['id']) ) {
                $where[] = " a.id = ? ";
                $p[] = $cfg['id'];
            }
        
        // if wher
        if ( count($where) > 0 ) {
            $sql .= " WHERE " . implode(" AND ",$where);
        }
        
        // limti
        $sql .= " LIMIT {$start},{$per} ";
        
        // total
        $total = true;

        
        // search
        $sth = $this->query($sql,$p,$total);
    
        // loop
        foreach ( $sth as $row ) {
        	$a = new aclItem('set',$row);
        	if ( $a->user->id ) {
				$this->items[] = $a;
        	}
        }
    
        // pager
        $this->setPager($total,$page,$per);
    
    }

	public function insert($type,$user,$id) {
		return $this->query("INSERT INTO `acl` SET `type` = ?, `user` = ?, `id` = ? ",array(
			$type,
			$user,
			$id
		));
	}

	public function delete($type,$user,$id) {
	
		return $this->query("DELETE FROM `acl` WHERE `type` = ? AND `user` = ? AND `id` = ? LIMIT 1 ",array(
			$type,
			$user,
			$id
		));
	}

	public function register($type,$dao) {
		self::$types[$type] = $dao;
	}

}

class aclItem extends \DaoDb {


    protected $data = array(
        'user' => false,
        'type' => false,
        'id' => false
    );
    
	protected $schema = array(
		'user' => array( 'type' => 'dao', 'class'=>'user' )
	);
	
	public function set($row) {
		
		// set
		parent::set($row);
		
		// types
		$types = acl::$types;
	
		// if it's there
		if ( array_key_exists($row['type'],$types) ) {
			$cl = "\\dao\\{$types[$row['type']]}";
			
			$this->private['asset'] = new $cl('get',array($row['id'],true));
		}
	
	}
    

    
}


?>