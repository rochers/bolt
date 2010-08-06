<?php


////////////////////////////////////
/// @breif database wrapper
////////////////////////////////////
class Database {

	// dbh
	private static $instance = false;
	private $dbh = false;

	// __construct is private so that 
	// you must use signlton
	private function __construct() {}
	
	public static function singleton() {
	
		// already created
		if ( !self::$instance ) {
			$class = __CLASS__;
			self::$instance = new $class();
		}
		
		// give it 
		return self::$instance;
	
	}

	public function connectToDb() {
	  
        if ( $this->dbh ) {
            return;
        }
        
        // get a db config
        $db = Config::get('db');
        
        // connect
        try {
            $this->dbh = new PDO("mysql:host={$db['host']};dbname={$db['name']}",$db['user'],$db['pass']);
        }
        catch ( PDOException $e ) {
            error_log( $e->getMessage() );
            exit("Could not connect to database ");
        }
        
        // no db
        if ( !$this->dbh ) {
            exit("Could not connect to database ");        
        }
	
	}


	/**
	 * query the db
	 * @method	query
	 * @param	{string} 		sql
	 * @param	{array}			params
	 * @param	{ref:array}		pager args
	 * @return	{object}		mysqli resuls object
	 */
	public function query( $sql, $params=array(), &$total=false ) {

		// connect to db
		// don't worry this is only done once
		$this->connectToDb();

		// need a query
		if ( !$sql ) die('no sql');
		
		// if selct and total
		if ( $total !== false AND stripos($sql,"SELECT") !== false ) {
            $sql = preg_replace("/^SELECT/i","SELECT SQL_CALC_FOUND_ROWS",trim($sql));
		}

		// run sql
		$sth = $this->dbh->prepare($sql);
		
		// eexecite
		$res = $sth->execute($params);

		// die
		if ( !$res OR ( $this->dbh->errorCode() != '00000' ) ) {
		
            // get
            $er = $sth->errorInfo();
                        
            // log
            if ( !defined("NO_SQL_ERROR") OR ( defined("NO_SQL_ERROR") AND NO_SQL_ERROR != true ) ) {
            	error_log("[SQL ERROR] ".preg_replace("/[\n|\t]+/",' ',$sql)." {$er[2]}\n");
            }
            
            
		}
		
		// return
		$r = array();
		
		// now lets see what happend
		if ( stripos($sql,'INSERT INTO') !== false ) {											
			$r = $this->dbh->lastInsertId();				
		}
		else if ( stripos($sql,'UPDATE') !== false ) {											
            $r = $res;
		}			
		else if ( $sth AND $sth->rowCount() > 0 ) {
            $r = $sth->fetchAll(PDO::FETCH_ASSOC);
		}
		
		// totoal
		if ( $total !== false ) {
		 
            // get total
            $t = $this->row("SELECT FOUND_ROWS() as t ");
            
            // set it 
            $total = $t['t'];
            
		}			

		// give back r;
		return $r;

	}
	

	/**
	 * perform a query an return the first row
	 * @method	row
	 * @param	{string}	sql
	 * @param	{array}		params
	 * @return	{array}		results array
	 */
	public function row( $sql, $params=array() ) {
	
		// run sql
		$sth = $this->query($sql,$params);

		// return
		if ( count($sth) > 0 ) {
			return $sth[0];
		}
		else {
			return array();	
		}
		
	}


	/**
	 * clean a string for mysql
	 * @method	clean
	 * @param	{string}	dirty string
	 * @return	{string}	clean string 
	 */
	public function clean($str) {
		return $this->dbh->real_escape_string($str );
	}		

}


// database mask abstract
abstract class DatabaseMask {

	/**
	 * query the db
	 * @method	query
	 * @param	{string} 		sql
	 * @param	{array}			params
	 * @param	{ref:array}		pager args
	 * @return	{object}		mysqli resuls object
	 */
	public function query( $sql, $params=array(), &$total=false ) {
		return $this->db->query($sql,$params,$total);	// now a passthrough to the Database class
	}
	

	/**
	 * perform a query an return the first row
	 * @method	row
	 * @param	{string}	sql
	 * @param	{array}		params
	 * @return	{array}		results array
	 */
	public function row( $sql, $params=array() ) {
		return $this->db->row($sql,$params);
	}


	/**
	 * clean a string for mysql
	 * @method	clean
	 * @param	{string}	dirty string
	 * @return	{string}	clean string 
	 */
	public function clean($str) {
		return $this->db->clean($str);
	}	

}


?>