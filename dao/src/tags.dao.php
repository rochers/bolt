<?php

namespace dao;

/////////////////////////////////////////////////
/// @brief tags dao
/// @extends DaoDb
/////////////////////////////////////////////////
class tags extends \DaoDb {


	/////////////////////////////////////////////////
	/// @brief set the list of tags
	///
	/// @param $=
	/// @return void
	/////////////////////////////////////////////////
	public function get($ns=false,$pred=false,$val=false) {
        
        // $items
        $items = $this->items;
        
        // if ns limit by ns
        if ( $ns !== false ) {
            
            foreach ( $items as $k => $tg ) {
                if ( $tg->namespace != $ns ) {                
                    unset($items[$k]);
                }
            }
            
        }
        
        // if pred limit by pred
        if ( $pred !== false ) {
            
            foreach ( $items as $k => $tg ) {
                if ( $tg->predicate != $pred ) {
                    unset($items[$k]);
                }
            }
            
        }        
        
        // if val limit by val
        if ( $val !== false ) {
            
            foreach ( $items as $k => $tg ) {
                if ( $tg->value != $val ) {
                    unset($items[$k]);
                }
            }
            
        }        
		        
        // return items
        return new tags('set',$items);
        
	}
	

	/////////////////////////////////////////////////
	/// @brief set the list of tags
	///
	/// @param list CSV string of tags
	/// @return void
	/////////////////////////////////////////////////
    public function set($list) {
    
        // no list
        if ( !$list ) {
            return;
        }
        
        if ( is_array($list) ) {
        	foreach ( $list as $tg ) {	
        		if ( is_object($tg) ) {
        			$this->items[] = $tg;
        		}
        		else {
        			list($ns,$pred,$val) = tag::parse($tg);
	        		$this->items[] = new tag('set',array($ns,$pred,$val));
        		}
        	}
        }
        else {
	    
	        // loop through and create the new tag
	        foreach ( explode(",",$list) as $t ) {
	            if ( mb_strpos($t,':') !== false ) {
	                
	                // get the namespace and value
	                list($ns,$pred) = explode(":",$t);
	                    
	                    $val = false;
	                
	                    // check for val
	                    if ( mb_strpos($pred,'=') !== false ) {
	                        list($pred,$val) = explode('=',$pred);
	                    }
	                
	                // create a new tag object
	                $tg = new tag('set',array($ns,$pred,$val));
	            
	                // now add to the list
	                $this->items[] = $tg;
	                
	            }            
	        }	                  
	        
		}
    
    	// set pager
    	$this->setPager( count($this->items), 1, 1 );
    
    }


	public function asArray() {
		$ary = array();
		foreach ( $this->items as $item ) {
			$ary[] = $item->raw;
		}
		return $ary;
	}
	
	public function __toString() {
		return implode(",", $this->asArray() );
	}
    
	/////////////////////////////////////////////////
	/// @brief add a tag to the list
	///
	/// @param ns namespace of the tag
	/// @param pred predicate of the tag
	/// @param $val value of the tag
	/// @return void
	/////////////////////////////////////////////////    
    public function add($ns,$pred=false,$val=false) {
    	if ( is_object($ns) ) {
    		$this->items[] = $ns;
    	}
    	else {
	        $this->items[] = new tag('set',array($ns,$pred,$val));
		}
    }
    
    
	/////////////////////////////////////////////////
	/// @brief remote a tag from the list
	///
	/// @param tag tag object to remove
	/// @return void
	/////////////////////////////////////////////////    
    public function remove($tag) {
        foreach ( $this->items as $k => $tg ) {
            if ( $tag->id == $tg->id ) {
                unset($this->items[$k]); break;
            }
        }
    }
    
    
	/////////////////////////////////////////////////
	/// @brief replace an existing tag with a new one
	///
	/// @param old tag object to replace
	/// @param new tag object to replace with
	/// @return void
	/////////////////////////////////////////////////    
    public function replace($old,$new) {
        foreach ( $this->items as $k => $tg ) {
            if ( $tg->id == $old->id ) {
                $this->items[$k] = $new;
            }
        }
    }
    

	/////////////////////////////////////////////////
	/// @brief remove all tags from the list
	///
	/// @return void
	/////////////////////////////////////////////////       
	public function removeAll() {
		$this->items = array();
	}

}

?>