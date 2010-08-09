<?php

namespace dao;

/////////////////////////////////////////////////
/// @brief parent class for all database
///        database dao objects
/////////////////////////////////////////////////
abstract class Db extends \Dao {

	// db
	protected $db = false;	

    /////////////////////////////////////////////////
    /// @brief construct a dao db  class
    ///
    /// @param type type of action to take after construct
    /// @param cfg configure options to pass to action after
    ///        construction
    /////////////////////////////////////////////////
	public function __construct($type=false, $cfg=array()) {

		// db
		$this->db = \Database::singleton();	

		// parent
		parent::__construct($type,$cfg);

	}	

	/// helper methods that go to db
	public function query( $sql, $params=array(), &$total=false ) { return $this->db->query($sql,$params,$total); }
	public function row( $sql, $params=array() ) { return $this->db->row($sql,$params); }
	public function clean($str) { return $this->db->clean($row); }


}

?>