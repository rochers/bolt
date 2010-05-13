<?php

namespace dao;

/////////////////////////////////////////////////
/// @brief tag dao
/// @extebds DaoDb
/////////////////////////////////////////////////
class tag extends \DaoDb {

    // list of special tags that 
    // should be checked against in the 
    // database
    private $special = array( 
        'filters' => array( 'object' => 'filters', 'value' => 'title' ) 
    );

	/////////////////////////////////////////////////
	/// @brief set the tag information
	///
	/// @param ns namespace of the tag
	/// @param pred predicate of the tag
	/// @param val value of the tag
	/// @return void
	/////////////////////////////////////////////////
	public function set($parts) {
	   
        // get parts of the tag
        if ( count($parts) == 3 ) {
    	    list($ns,$pred,$val) = $parts;        
        }
        else {
	        list($ns,$pred) = $parts;        
        }

		
        // raw
        $raw = "{$ns}:{$pred}";
        
            // if val
            if ( $val !== false ) {
                $raw .= "={$val}";
            }
        
        // check if the namespace is special
        if ( isset($this->special[$ns]) ) {
        
            // get the data 
            $o = new $this->special[$ns]['object']('get',array($pred));
            
            // set value
            $val = $o->{$this->special[$ns]['value']};
        	       
        }
        else {
            
            // set our own data
            $this->data['id'] = md5($raw);
            
            // if no value we set as the pred
            if ( !$val ) {
                $val = $pred;
            }
            
        }
        
        // raw
        $this->data['raw'] = $raw;
        $this->data['namespace'] = $ns;
        $this->data['predicate'] = $pred;
        $this->data['value'] = $val;
        
    }

	public static function parse($str) {
		
		// start out
		$ns = $pred = $val = false;
		
		// find ns 
		list($ns,$pred) = explode(':',$str);
			
			// check for val
			if ( mb_strpos($pred,'=') !== false ) {
				list($pred,$val) = explode("=",$pred);
			}
	
		// give back
		return array($ns,$pred,$val);
	
	}

}

?>