<?php

abstract class Dao {

	protected $data = array();
	protected $items = array();
	protected $private = array();	
	protected $schema = false;
	protected $changes = false;
	
	// public stuff for paging
	public $_total = 0;
	public $_pages = 0;
	public $_page = 1;
	public $_per = 20;
	public $_start = 1;
	public $_end = 20;
	
	public function __construct($type=false,$cfg=array()) {
	
		// if get param
		if ( $type == 'get' ) {		
			call_user_func_array(array($this,'get'),(array)$cfg);
		}
		else if ( $type == 'set' ) {
			$this->set($cfg);
		}		
		
		// transform our schema
		if ( is_array($this->schema) ) {
			foreach ( $this->schema as $k => $v ) {
				if ( isset($v['map']) ) {
					if ( is_string($v['map']) ) {
					
						$f = eval($v['map']);
					
						$this->schema[$k]['map'] = call_user_func($f);
					}
				}
			}
		}	
	
	}
	
	
	/////////////////////////////////////////////////
	/// @brief MAGIC get a property from data array
	/// 
	/// @param name name of the property to get from
	///        the data array
	/// @return value if property exists. false
	///         if property does not exist
    /////////////////////////////////////////////////
	public function __get($name) {
	
        // data
        $data = array_merge($this->data,$this->private);

		// if it exists
		if ( array_key_exists($name,$data) ) {		
		
			// objectify
			if ( is_array($data[$name]) ) {
				return $this->objectify($data[$name]);
			}
			
			// plain
            return $data[$name];										
            
		}

        // check for _
        else if ( mb_strpos($name,'_') !== false ) {
            
            // explode out 
            $parts = explode('_',$name);
            
            // set parts
            $ary = $parts[0];
            $key = $parts[1];
            
            // what
            if ( array_key_exists($ary,$data) AND is_array($data[$ary]) AND array_key_exists($key,$data[$ary]) ) {
            
            	// check if they want a key if the array
				if ( isset($parts[2]) AND is_array($data[$ary][$key]) ) {
            	            	
            		// yes
            		if ( array_key_exists($parts[2],$data[$ary][$key]) ) {
            			return $this->objectify($data[$ary][$key][$parts[2]]);
	            	}
	            	else {
	            		return false;
	            	}
            	
     	       }
     	       
				// just return the array
				return $this->objectify($data[$ary][$key]);   
				
			}
			
        }
       
        
        // nope
        return false;

	}	
	
	
	/////////////////////////////////////////////////
	/// @brief MAGIC set a property from data array
	/// 
	/// @param name name of the property to set in
	///        the data array
	/// @param val value to set property to
	/// @return null
    /////////////////////////////////////////////////	
	public function __set($name,$val) {

		// current
		$cur = $this->{$name};	   
	   
	   	// find some data 
		if ( array_key_exists($name,$this->data)) {
		 	$this->data[$name] = $val;
		}
		else if ( array_key_exists($name,$this->private) ) {
			$this->private[$name] = $val;
		}
			
        // check for _
        else if ( mb_strpos($name,'_') ) {
            
			// explode out 
            $parts = explode('_',$name);
            
            // set parts
            $ary = $parts[0];
            $key = $parts[1];
            
            // what
            if ( !isset($this->data[$ary]) ) {
            	$this->data[$ary] = array();
            }
            
        	// check if they want a key if the array
			if ( isset($parts[2]) ) {
				if ( !is_array($this->data[$ary][$key]) ) {
					$this->data[$ary][$key] = array();
				}
        	            	
      			$this->data[$ary][$key][$parts[2]] = $val;
        	
			}
			else {
				// just return the array
				$this->data[$ary][$key] = $val;				
			}
            
        }
        
		// add it 
		if ( isset($this->trackChanges) AND $this->trackChanges == true AND $val != $cur AND $name != 'changelog' ) {
			$this->changes[$name] = array( 'new' => $val, 'old' => $cur );
		}

	}		
	
	
	/////////////////////////////////////////////////
	/// @brief MAGIC call a function
	/// 
	/// @return mixed
    /////////////////////////////////////////////////		
    public function __call($name,$args) {
    	
    	// return
    	$r = false;
    	
    	// what to return
    	switch($name) {
    	
    		// short
    		case 'short':
    			
    			// l
    			$str = array();
    			$l = 0;
			    
			    // no spaces
			    if ( mb_strpos($this->{$args[0]},' ') === false ) {
			    	return substr($this->{$args[0]},0,$args[1]-3).'...';
			    }

    			// how many 
    			foreach ( explode(" ",$this->{$args[0]}) as $w ) {	
    				if ( $l+strlen($w) > $args[1] ) {
    					return trim(implode(" ",$str)).'...';
    				}
    				$str[] = $w; $l += strlen($w);    				
    			}
    			
    			// str
    			$s = implode(" ",$str);
    				
    				// if total str is too big
    				if ( strlen($s) > $args[1] ) {
    					$s = substr($s,0,$args[1]-3).'...';
    				}
    			
    			return $s;
    	
    		// date
    		case 'date':
    		
    			// give r
    			$ts = $args[0];
    			$frm = (isset($args[1])?$args[1]:'m/d/Y');
    			
    				// if not a ts
    				if ( !$this->{$ts} ) {
    					return false;
    				}
    			
    			// give it 
    			return date($frm,$this->{$ts});
    			
    		// decode
    		case 'decode':
				return html_entity_decode($this->{$args[0]},ENT_QUOTES,'utf-8');
				
    		// decode
    		case 'encode':
				return htmlentities($this->{$args[0]},ENT_QUOTES,'utf-8');
    	
    		// pop
    		case 'push':
    			
    			// get some stuff
    			$ary = $this->{$args[0]};
    			$val = $args[1];
				$key = (isset($args[2])?$args[2]:false);
    			
    			// if ary === false we assume it's just empty
    			if ( $ary === false ) {
    				$ary = array();
    			}
    			
    			// is object
    			if ( is_object($ary) AND method_exists($ary,'asArray') ) {
    				$ary = $ary->asArray();
    			}
    			
				// need it to be an array
				if( !is_array($ary) ) {
					return false;
				}
    		
    			// add it
				if ( $key ) {
					$ary[$key] = $val;
				}
				else {
					$ary[] = $val;
				}
				
				// reset
				$this->{$args[0]} = $ary;
				
				// return array
				return $ary;
    			
    	
    	};
    
    
    }
	
	
	/////////////////////////////////////////////////
	/// @brief MAGIC return object as json array
	/// 
	/// @return data array as json string
    /////////////////////////////////////////////////	
	public function __toString() {
		return json_encode( $this->asArray() );
	}	
	
	
	/////////////////////////////////////////////////
	/// @breif default get action
	///
	/// @return full data array
	/////////////////////////////////////////////////
	public function get() {
		return $this->data;
	}
	
	
	/////////////////////////////////////////////////
	/// @brief default set action
	///
	/// @param $data array of data to set	
	/// @return void
	/////////////////////////////////////////////////
	public function set($data){

		// set the data
		foreach ( $data as $k => $v ) {
			$this->data[$k] = $v;
		}
		
		// if schema is defined, loop
		// through and format schema
		if ( $this->schema !== false ) {
            foreach ( $this->schema as $key => $info ) { 
                if ( array_key_exists($key,$this->data) ) {          
                    
                    // value holder
                    $value = $this->data[$key];
                              
                    // based on data type do the transform
                    if ( isset($info['type']) ) {
                        
                        // which type
                        switch ($info['type']) {
                        
                            // json we need to decode
                            case 'json':                             
                                $value = ( is_array($this->data[$key] ) ? $this->data[$key] : json_decode($this->data[$key],true) ); 
                                if ( is_null($value) ) {
                                	$value = false;
                                }
                                break;
                                
                            // tags need to be turned into
                            // a tags array
                            case 'tags':                            
                                $value = new \dao\tags('set',$this->data[$key]); break;
                                
                            // dao
                            case 'dao':
                            	$cl = "\\dao\\{$info['class']}";
                                $value = new $cl('get',array($this->data[$key])); break;                        
                            
                            // datetime
                            case 'timestamp':
                            
                            	// check user for a tzoffset
						        $u = Session::getUser();
						        
                            	// ago
                            	$this->private['f_'.$key.'_ago'] = ago($value);
						        
						        // offset
						        if ( $u AND $u->profile_tzoffset ) {
						        	$value += $u->profile_tzoffset;
						        }
						        
						        // reset the main value
						        $this->data[$key] = $value;						       
                            
                            	// info
                            	$info['private'] = true;
                            	$info['key'] = 'f_'.$key;                            
                            	
                            	// value
                            	$value = date(DATE_LONG_FRM,$value);
                            	
                            	// break
                            	break;
                            	
                            
                        };
                        
                        // check if they want to set as new key
                        if ( array_key_exists('key',$info) ) {
                            $this->data[$info['key']] = $value;
                            $key = $info['key'];
                        }
                        else {
                            $this->data[$key] = $value;
                        }
                        
                    }
                    
                    // if private, needs to be requested directly
                    if ( isset($info['private']) ) {                   
                        $this->private[$key] = $this->data[$key];
                        unset($this->data[$key]);
                    }
                    
                }
            }		
		}
		
	}

	/////////////////////////////////////////////////
	/// @brief normalize the data for insert
	///
	/// @return array
	/////////////////////////////////////////////////	
	public function normalize() {
	
		// data
		$data = $this->data;
	
		// if schema is defined, loop
		// through and format schema
		if ( $this->schema !== false ) {
            foreach ( $this->schema as $key => $info ) { 
//                if ( $this->$key ) {          
                    
                    // value holder
                    $value = $this->$key;
                              
                    // based on data type do the transform
                    if ( isset($info['type']) ) {
                        
                        // which type
                        switch ($info['type']) {
                        
                            // json we need to decode
                            case 'json': 
                                $value = json_encode($data[$key]); break;
                                
                            // tags need to be turned into
                            // a tags array
                            case 'tags':                            
                                $value = (string)$data[$key]; break;
                                
                            // dao
                            case 'dao':
                            	if ( is_object($data[$key]) ) {
                            		$id = p('id','id',$info);                            	
									$value = $data[$key]->{$id}; 
								}
								break;
								
							// timestsamp
							case 'timestamp':
							
                            	// check user for a tzoffset
						        $u = Session::getUser();
						        
						        // offset
						        if ( $u AND $u->profile_tzoffset ) {
						        	$value -= $u->profile_tzoffset;
						        }							
							
								break;
                            
                        };
                        
                    }
                    
                    // if private, needs to be requested directly
                    if ( isset($info['private']) ) {                   
                        if (isset($data[$key]) && isset($this->private[$key])) { 
                        	$data[$key] = $this->private[$key];
                        }
                    }
                    else {
                    	$data[$key] = $value;
                    }
                    
//                }
            }		
		}	
		
		// give back data
		return $data;		
	
	}


	/////////////////////////////////////////////////
	/// @brief turn an array into an object
	///
	/// @param array the array to turn into an object
	/// @return stdclass object
	/////////////////////////////////////////////////	
	private function objectify($array) {
	
		if(!is_array($array) OR is_numeric(key($array)) ) {
			return $array;
		}
	
		$object = new DaoMock();
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
	
	
	/////////////////////////////////////////////////
	/// @brief print object as an array
	///
	/// @param key return only single key as array
	/// @return data object value as array
	/////////////////////////////////////////////////	
	public function asArray($key=false) {
        
        // resp
        $resp = array();
        
        // if there are items
        // go through and get array
        if ( count($this->items) > 0 ) {
            foreach ( $this->items as $item ) {
                $i = $item->asArray();
                $i['_item'] = 'item';
                $resp[] = $i;
            }
    	}
    	else {
    	
            // get it 
    		if ( $key AND array_key_exists($key,$this->data) ) {
    			$resp = $this->data;
    		}
    		else {
    			$resp = $this->data;
    		}
    		
        }
    		
		// make sure we have no objects
		foreach ( $resp as $k => $v ) {
		  if ( is_object($v) ) {
		      $resp[$k] = $v->asArray();
		  }
		  else if ( $v === false ) {
		      unset($resp[$k]);
		  }
		}
		
		// give
		return $resp;
		
	}
	
	
	/////////////////////////////////////////////////
	/// @brief get data array
	/// 
	/// @return data aray;
	/////////////////////////////////////////////////
	public function getData() {
		return $this->data;
	}
	
	
	/////////////////////////////////////////////////
	/// @brief get schema array
	/////////////////////////////////////////////////
	public function getSchema() {
		return $this->schema;
	}	
	
	/////////////////////////////////////////////////
	/// @brief set the pager information for list functions
	///
	/// @param total total number of pages
	/// @param page current page
	/// @param per number of items per page
	/// @return void
	/////////////////////////////////////////////////
	protected function setPager($total,$page,$per) {
				
		$this->_total = (int)$total;
		$this->_page = $page;
		$this->_per = $per;
		
		// pages
		$this->_pages = ($page>0?ceil($total/$per):1);
	
		// sttart
		$this->_start = ( ($page-1) * $per )+1;
		$this->_end = ($this->_start + $per) - 1;
		
			if ( $this->_end > $this->_total ) {
				$this->_end = $this->_total;
			}
	
	}
	
	/////////////////////////////////////////////////
	/// @brief get the # of the first item in page set
	///
	/// @return formated number of start item
	/////////////////////////////////////////////////	
	public function getStart() {
		return number_format((double)$this->_start);
	}


	/////////////////////////////////////////////////
	/// @brief get the # of the last item in page set
	///
	/// @return formated number of last item
	/////////////////////////////////////////////////	
	public function getEnd() {
		return number_format((double)$this->_end);
	}

	
	/////////////////////////////////////////////////
	/// @brief get total number of items in set
	///
	/// @return formated number of total items in set
	/////////////////////////////////////////////////		
	public function getTotal() {
		return number_format((double)$this->_total);
	}	
	
	
	/////////////////////////////////////////////////
	/// @brief get total number of pages
	///
	/// @return formated number of total items in set
	/////////////////////////////////////////////////		
	public function getPages() {
		if ( $this->_pages==0 ) { return 1; }	
		return number_format((double)$this->_pages);
	}	
	
	
	/////////////////////////////////////////////////
	/// @brief get total number of pages
	///
	/// @return formated number of total items in set
	/////////////////////////////////////////////////		
	public function getPage() {
		if ( $this->_page==0 ) { return 1; }
		return number_format((double)$this->_page);
	}		
	

	/////////////////////////////////////////////////
	/// @brief get total number of pages
	///
	/// @return formated number of total items in set
	/////////////////////////////////////////////////		
	public function getPer() {
		return number_format((double)$this->_per);
	}		
	
	/////////////////////////////////////////////////
	/// @brief get the next page number in the page set
	///
	/// @return int of next page number
	/////////////////////////////////////////////////		
	public function nextPage() {
		if ( $this->_page == $this->_pages ) {
			return false;
		}
		return $this->_page + 1;
	}
	
	
	/////////////////////////////////////////////////
	/// @brief get the prev page number in the page set
	///
	/// @return int of the prev page 
	/////////////////////////////////////////////////		
	public function prevPage() {
		if ( $this->_page == 1 ) {
			return false;
		}
		return $this->_page - 1;		
	}
	
	
	/////////////////////////////////////////////////
	/// @brief array containing all pages in the page set
	///
	/// @return array of page numbers
	/////////////////////////////////////////////////		
	public function range() {
		if ( $this->_total == 0 ) { return array(); }
		return range(1,$this->_pages);
	}
	
	
	/////////////////////////////////////////////////
	/// @brief get item value at given index
	///
	/// @param idx index number to check for value
	/// @return value at given index
	/////////////////////////////////////////////////		
	public function item($idx=0) {
		return $this->items[$idx];
	}


	/////////////////////////////////////////////////
	/// @brief reset pointer to first item in set
	///
	/// @return void
	/////////////////////////////////////////////////	
    public function rewind() {
        reset($this->items);
    }


	/////////////////////////////////////////////////
	/// @brief get the current item pointer 
	///
	/// @return value of current pointer item
	/////////////////////////////////////////////////	
    public function current() {
        $var = current($this->items);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief key value of current pointer item
	///
	/// @return value of pointer item
	/////////////////////////////////////////////////	
    public function key() {
        $var = key($this->items);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief go to next item in the set
	///
	/// @return value of next item in set
	/////////////////////////////////////////////////	
    public function next() {
        $var = next($this->items);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief check if the current value is valid
	///
	/// @return bool if current value is valid
	/////////////////////////////////////////////////	
    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }


}

//
class DaoMock implements Iterator {

	private $data = array();
	
	public function __set($name,$val) {
		$this->data[$name] = $val;
	}
	public function __get($name) {
		if ( array_key_exists($name,$this->data) ) {
			return $this->data[$name];
		}
		return false;
	}
	public function asArray() {
		$a = array();
		foreach ( $this->data as $k => $v ) {
			if ( is_object($v) ) {
				$v = $v->asArray();
			}
			$a[$k] = $v;
		}
		return $a;
	}
	public function exists($key) {
		if ( isset($this->data[$key]) ) {
			return true;
		}
		return false;
	}
	
	/////////////////////////////////////////////////
	/// @brief get item value at given index
	///
	/// @param idx index number to check for value
	/// @return value at given index
	/////////////////////////////////////////////////		
	public function item($idx=0) {
		return $this->data[$idx];
	}


	/////////////////////////////////////////////////
	/// @brief reset pointer to first item in set
	///
	/// @return void
	/////////////////////////////////////////////////	
    public function rewind() {
        reset($this->data);
    }


	/////////////////////////////////////////////////
	/// @brief get the current item pointer 
	///
	/// @return value of current pointer item
	/////////////////////////////////////////////////	
    public function current() {
        $var = current($this->data);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief key value of current pointer item
	///
	/// @return value of pointer item
	/////////////////////////////////////////////////	
    public function key() {
        $var = key($this->data);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief go to next item in the set
	///
	/// @return value of next item in set
	/////////////////////////////////////////////////	
    public function next() {
        $var = next($this->data);
        return $var;
    }


	/////////////////////////////////////////////////
	/// @brief check if the current value is valid
	///
	/// @return bool if current value is valid
	/////////////////////////////////////////////////	
    public function valid() {
        $var = $this->current() !== false;
        return $var;
    }	
	
}



?>