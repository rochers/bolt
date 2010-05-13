<?php

abstract class DaoWebservice extends Webservice {
	
	protected $webservice = false;
	protected $data = array();
	protected $items = array();

	public function __construct($type='get',$cfg=array()) {
		
		// db
		//$this->db = Database::singleton();
		
		// if get param
		if ( $type == 'get' ) {		
			call_user_func_array(array($this,'get'),(array)$cfg);
		}
		else if ( $type == 'set' ) {
		
			$this->set($cfg);
		}		
		
	}
	
	public function get() {
		return $this->data;
	}
	
	public function set($data){
		$this->data = $data;
	}

	public function __get($name) {
		if ( array_key_exists($name,$this->data) ) {		
						
			// if it's an array we want to turn
			// it into an object
			if ( is_array($this->data[$name]) ) {
			
				// set the new val
				$this->data[$name] = $this->objectify($this->data[$name]);
	
			}
			
			// hi val
			return $this->data[$name];
	
		}
		else {
			return false;
		}
	}
	
	private function objectify($array) {
	
		if(!is_array($array) OR is_numeric(key($array)) ) {
			return $array;
		}
	
		$object = new stdClass();
		if (is_array($array) && count($array) > 0) {
		  foreach ($array as $name => $value) {
		        $object->$name = $this->objectify($value);
		  }
	      return $object; 
		}
	    else {
	      return false;
	    }


	}		

	
	public function __set($name,$val) {
		$this->data[$name] = $val;
	}	
	
	public function item($idx=0) {
		return $this->items[$idx];
	}

    public function rewind() {
        reset($this->items);
    }

    public function current() {
        $var = current($this->items);
        return $var;
    }

    public function key() {
        $var = key($this->items);
        return $var;
    }

    public function next() {
        $var = next($this->items);
        return $var;
    }

    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }

}

?>