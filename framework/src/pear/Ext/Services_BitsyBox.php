<?php

//////////////////////////////////////////////////////////////////////
///
/// @mainpage 
/// @section BitsyBox Client API
/// Services Package for interaction with the BitsyBox API <br>
/// For more information visit http://bitsybox.com/fe/support/docs/section/815	
/// 
/// @subsection Classes
///  <ul> 
///   <li>Services_BitsyBox </li>
///  </ul>
///
/// @subsection Return Types
///  <ul> 
///    <li>Services_BistyBox_Field</li>
///    <li>Services_BistyBox_Item</li>
///    <li>Services_BitsyBox_Module</li>
///  </ul>
/// 
/// @version 3.0.1
///
/// @date 2009-08-28
///
/// @section Contact 
///  Travis Kuhl <travis@bitsybox.com> <br>
///  Scott Rocher <scott@bitsybox.com>
/// 
/// @section License 
///   2009 BitsyBox <br>
///   Released under PHP License 3.01 <http://www.php.net/license/3_01.txt> <BR><BR>
///
///  THIS SOFTWARE IS PROVIDED BY BITSYBOX "AS IS" AND 
///  ANY EXPRESSED OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO,
///  THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A 
///  PARTICULAR PURPOSE ARE DISCLAIMED.  IN NO EVENT SHALL BITSYBOX 
///  OR ITS CONTRIBUTORS BE LIABLE FOR ANY DIRECT, 
///  INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES 
///  (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR 
///  SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION)
///  HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT,
///  STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)
///  ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED
///  OF THE POSSIBILITY OF SUCH DAMAGE.
/// 
///////////////////////////////////////////////////////////////////////
 
//////////////////////////////////////////////////////////////////////
/// @class Services_BitsyBox
/// @breif Base BitsyBox Client Class
//////////////////////////////////////////////////////////////////////
class Services_BitsyBox {

	/* static:properties */
	static $VERSION = '3.0.1';

    /* public:properties */
    public $key;
    public $secret;
    public $error = false;
    
    /* private:properties */
    public $baseUrl = "http://api1.bitsybox.com/rest/v";
    private $apiVersion = '2.1';
    
    // referance to cache object
    private $caching = true; 
    public $cache = false;    
    
    // deprecated caching properties
    public $ttl = 300;							/// @deprecated    
    private $cacheDir = "/tmp/bitsybox/";		/// @deprecated
    private $cacheTTL = 43200; 					/// @deprecated
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Construct the bitsybox class
    ///
    /// @param $key Site API Key
    /// @param $secret Site API Secret
    /// @param $p Array of Options
    ///
    /// Options  
    ///   - $version / float / Version of the API to use 
    ///   - $cacheClass / string / name of caching class (default:Services_BistyBox_CacheFile) 
    ///   - $cacheArgs / array / list of arguments to pass to the cache class 
    ///
    /// @deprecated 
    ///   - $cacheDir / string / Directory to store cache files
    ///   - $caching / bool / Use caching
    ///   - $cacheTTL / int / Number of seconds to store cache files
    ///
	//////////////////////////////////////////////////////////////////////
    public function __construct($key,$secret,$p=array()) {
    
        // need key and secret
        if ( !$key OR !$secret ) {
            return error_log('Site API Key and Secret Token are required');
        }
    
        // save them
        $this->key = $key;
        $this->secret = $secret;
        
        // is version set
        if ( isset($p['version']) ) {
            $this->apiVersion = $p['version'];
        }
        
        // caching 
        if ( array_key_exists('caching',$p) ) {
            $this->caching = $p['caching'];
        }        
        
        // cache 
        if ( !isset( $p['cacheClass'] ) ) {
        
        	// class
        	$p['cacheClass'] = "Services_BitsyBox_CacheFile";               	

		        // is cache dir set
		        if ( isset($p['cacheDir']) ) {
		            $this->cacheDir = $p['cacheDir'];
		        }
        
		        // default cache ttl 
		        if ( isset($p['cacheTTL']) ) {
		            $this->cacheTTL = $p['cacheTTL'];
		            $this->ttl = $p['cacheTTL'];
		        }           
		        
			// args
			$p['cacheArgs'] = array(
				'dir' => $this->cacheDir,
				'ttl' => $this->ttl
			);
        
        }
        
        // check for a cache class
        if ( $this->caching !== false ) {
        	
        	// make sure the class exists
        	if ( class_exists($p['cacheClass'],true) ) {
        		
        		// caching arguments
        		$a = $this->p('cacheArgs',array(),$p);
        		
        		// instanciate it 
        		$this->cache = new $p['cacheClass']($a);
        	
        		// make sure iServices_BitsyBox_Caching is implemented
        		if ( !( $this->cache instanceof iServices_BistyBox_Caching ) ) {
        		
        			// no caching
        			$this->caching = false;
        			
        			// set error 
        			$this->error = "{$p['cacheClass']} does not implement iServices_BitsyBox_Caching";
        			
        		}
        		
        	}
        	else {
        	
        		// error them 
	        	$this->error = "{$p['cacheClass']} does not exists. Make sure you included the file.";	
        	
        	}
        
        }        
        
        // add version to base url
        $this->baseUrl .= $this->apiVersion . '/';
        
    }

	//////////////////////////////////////////////////////////////////////
    /// @breif Get a list of all pages for a site
    ///
    /// @return array of Services_BitsyBox_Module objects
    /// @param $p Options
    ///    
    ///  Options
    ///   - $ttl / int / number of seconds to cache a result. overrides global ttl 
    ///
	//////////////////////////////////////////////////////////////////////
    public function getPages($p=array()) {
    
        // add our type
        $p['type'] = 'page';
    
        // get a list of all containers that are 
        // marked as pages
        $containers = $this->getContainers($p);
        
        	// if flase
        	if ( !$containers ) {
        		return false;
        	}
    
        // pages
        $pages = array();
    
        // what do we get back 
        foreach ( $containers['containers'] as $c ) {	
            $pages[] = new Services_BitsyBox_Module($c,$this);
        }
        
        // give back
        return $pages;
    
    }
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get a single page
    ///
    /// @return Services_BitsyBox_Module object
    /// @param $p Options
    ///    
    ///  Options
    ///   - $id / int / Id of the page
    ///   - $key / string / Key of the page
    ///   - $ttl / int / number of seconds to cache a result. overrides global ttl 
    ///
	//////////////////////////////////////////////////////////////////////
    public function getPage($p=array()) {
    
        // make the request
        $container = $this->getContainer($p);
    
        	// if flase
        	if ( !$container ) {
        		return false;
        	}        
    
        // return module
        return new Services_BitsyBox_Module($container['container'],$this);    
    
    }
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get a single module
    ///
    /// @return Services_BitsyBox_Module object
    /// @param $p Options
    ///    
    ///  Options
    ///   - $id / int / Id of the module to select
    ///   - $key / string / Key id of the module
    ///   - $ttl / int / number of seconds to cache a result. overrides global ttl 
    ///
	//////////////////////////////////////////////////////////////////////
    public function getModule($p=array()) {
    
        // make the request
        $container = $this->getContainer($p);
        
        	// if flase
        	if ( !$container ) {
        		return false;
        	}            
    
        // return module
        return new Services_BitsyBox_Module($container['container'],$this);    
        
    
    }    
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get a list of modules
    ///
    /// @return array of Services_BitsyBox_Module object
    /// @param $p Options
    ///    
    ///  Options
    ///   - $modules / array / list of modules to retrieve 
    ///      - $ttl / int / number of seconds to cache a result. overrides global ttl     
    ///      - $id / int / Id of the module to select
    ///   - $key / string / Key id of the module
    ///
	//////////////////////////////////////////////////////////////////////
    public function getModules($p=array()) {
    
    	// modules
    	$modules = array();

		// go through the list 
		foreach ( $p['modules'] as $m ) {
		
			// get
			$c = $this->getContainer($m);
		
			// set
			if ( $c ) {
				$modules[] = new Services_BistyBox_Module($c['container'],$this);
			}
			
		}
		
		// give back
		return $modules;
	   
    }
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get a single item
    ///
    /// @return Services_BistyBox_Item object
    /// @param $p Options
    ///    
    ///  Options
	///   
	///   - $id / int / Id of the item to select
    ///   - $key / string / Key id of the item
    ///   - $recursive / bool / expand all child items
    ///   - $page / int / page of expanded items to get
    ///   - $per / int / number of expanded child items to get
	///   - $ttl / int / number of seconds to cache a result. overrides global ttl         
    ///
	//////////////////////////////////////////////////////////////////////
	public function getItem($p) {
	
        // cache ttl
        if ( array_key_exists('ttl',$p) ) {
            $this->ttl = $p['ttl'];
        }		
	
        // how to make the reqiest
        $type = ( array_key_exists('id',$p) ? 'id' : 'key' );
	
		// make our request
		$r = $this->sendRequest(
			"site/item/{$type}/{$p[$type]}",
			array(
    			'recursive'	=> $this->p('recursive','',$p),
    			'page' 		=> $this->p('page',1,$p),
    			'per'		=> $this->p('per',false,$p)				
			)
		);
		
        	// if flase
        	if ( !$r ) {
        		return false;
        	}			

		// unset
		unset($r['item']['container']);
	
		// get some item
		return new Services_BistyBox_Item($r['item'],$this);
			
	}
    
    
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get a list of items
    ///
    /// @return array of Services_BistyBox_Item objects
    /// @param $p Options
    ///    
    ///  Options
	///   
	///   - $items / array / list of items to retrieve
	///      - $id / int / Id of the item to select
    ///      - $key / string / Key id of the item
    ///      - $recursive / bool / expand all child items
    ///      - $page / int / page of expanded items to get
    ///      - $per / int / number of expanded child items to get
	///   - $ttl / int / number of seconds to cache a result. overrides global ttl         
    ///
	//////////////////////////////////////////////////////////////////////
	public function getItems($p) {
		
		// items
		$items = array();
	
		foreach ( $p['items'] as $i ) {
			$items[] = $this->getItem($i);
		}
		
		// give backs
		return $items;
	
	}



	//////////////////////////////////////////////////////////////////////
    /// @breif Get a container. 
    ///
    /// @return array of container items
    /// @param $p Options
    ///    
    ///  Options
	///   
	///   - $type / string / type of containers to get
	///   - $ttl / int / number of seconds to cache a result. overrides global ttl         
    ///
	//////////////////////////////////////////////////////////////////////
    public function getContainers($p=array()) {
        
        // cache ttl
        if ( array_key_exists('ttl',$p) ) {
            $this->ttl = $p['ttl'];
        }
    
        // make our call
        $r = $this->sendRequest(
        		"site/containers",
        		array(
        			'type'		=>$this->p('type',false,$p)
        		)
        	);

		// bad
		if ( !$r ) {
			return false;
		}
         
        
        // give back
        return $r;
        
    }

	//////////////////////////////////////////////////////////////////////
    /// @breif Get a single container
    ///
    /// @return container item
    /// @param $p Options
    ///    
    ///  Options
	///   
	///   - $id / int / Id of the item to select
    ///   - $key / string / Key id of the item
    ///   - $recursive / bool / expand all child items
    ///   - $page / int / page of expanded items to get
    ///   - $per / int / number of expanded child items to get
	///   - $ttl / int / number of seconds to cache a result. overrides global ttl         
    ///
	//////////////////////////////////////////////////////////////////////    
	public function getContainer($p=array()) {
    
        // cache ttl
        if ( array_key_exists('ttl',$p) ) {
            $this->ttl = $p['ttl'];
        }    
    
        // how to make the reqiest
        $type = ( array_key_exists('id',$p) ? 'id' : 'key' );
    
        // make the request
        $r = $this->sendRequest(
        	"site/container/{$type}/".$p[$type],
        	array(
    			'recursive'	=> $this->p('recursive','',$p),
    			'page' 		=> $this->p('page',1,$p),
    			'per'		=> $this->p('per',false,$p)
        	)
        );
        
        // give back
        return $r;
    
    }
     

	//////////////////////////////////////////////////////////////////////
    /// @breif Get the current version of the API using
    ///
    /// @return API version
	//////////////////////////////////////////////////////////////////////
    public function getApiVersion() {
        return $this->apiVersion;
    }


	//////////////////////////////////////////////////////////////////////
    /// @breif Send a request to the API server
    ///
    /// @return array containing the API results
    /// @param $uri moudle, method and path of the request
    /// @param $params query params for reuqest
    /// @param $post post params for request
    /// @param $headers additional headers for the request
	//////////////////////////////////////////////////////////////////////
    public function sendRequest($uri,$params=array(),$post=array(),$headers=array()) {
    
        // create a uniqe cid for this all
        $cid = md5( $this->key . $uri . serialize($params) . serialize($post) );
        
            // are we chacing
            if ( $this->p('clearCache',false,$_GET) != 1 AND $this->cache AND ( $cache = $this->cache->get($cid) )) {    
                return $cache;
            }              
    
        // generate sig
        $sig = md5($this->key.$this->secret.trim($uri,'/'));
        
        // headers 
        $headers['x-bitsybox-key'] = $this->key;
        $headers['x-bitsybox-sig'] = $sig;
        $headers['User-Agent'] = 'Services_BistyBox_'.self::$VERSION;
                  
        
        // url 
        $url = $this->baseUrl . $uri; 
       
        
        // params
        $p = array('output=php',"key={$this->key}","sig={$sig}");
    
            // add our params 
            foreach ( $params as $k => $v ) {
                if ( $k AND $v ) {
                    $p[] = $k."=".rawurlencode($v);
                }
            }

        // append
        $url .= '?'.implode('&',$p);            

        // new curl request
        $ch = curl_init();

        // set some stuff
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);

        // add headers
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    
        // add params
        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        
        // make the request
        $body = curl_exec($ch);         
                    
		// bad curl call
        if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
        	
        	// show error
        	$msg = 'Services BitsyBox HTTP ' . curl_getinfo($ch,CURLINFO_HTTP_CODE);
        	
        	// error
        	$this->error = $msg;
        	
        	// log
			error_log($msg);

            // are we chacing
            if ( $this->cache AND ( $cache = $this->cache->get($cid) ) ) {    
				return unserialize($cache);
            }    
            else {
            	return false;
            }   				

        }    
        
        // close curl
        curl_close($ch);           
        
        // get result
        $result = @unserialize($body);
    
        // check it 
        if ( !$result OR $result['stat'] != '1') {
        
        	// log error
            $msg = error_log('Services BitsyBox error '.$result['error']['message']);
            
            // attach error
            $this->error = $msg;
            
            // are we chacing
            if ( $this->cache AND ( $cache = $this->cache->get($cid) ) ) {    
                return $cache;
            }    
            else {
            	return false;
            }            

        }    

            // if caching 
            if ( $this->cache AND $this->ttl !== false ) {
                $this->cache->set($cid,$result['results'],$this->ttl);
            }                    
        
        // error
        $this->error = false;
        
        // give back
        return $result['results'];
    
    }

	//////////////////////////////////////////////////////////////////////
    /// @breif Send dump request to the cache class
	//////////////////////////////////////////////////////////////////////
	public function dumpCache() {
		if ( $this->cache ) {
			$this->cache->dump();
		}
	}
       
    
	//////////////////////////////////////////////////////////////////////
    /// @breif Get the value of an array
    ///
    /// @return value of param
    /// @param $key key of the array
    /// @param $default default value if the key does not exist 
    /// @param $array array to use
    /// @param $filter regex filter to run value through
	//////////////////////////////////////////////////////////////////////
    function p($key,$default=false,$array=false,$filter=false) {
    
        // check if key is an array
        if ( is_array($key) ) {
        
            // alawys 
            $key = $key['key'];
            
            // check for other stuff
            $default = p('default',false,$key);
            $array = p('array',false,$key);
            $filter = p('filter',false,$key);
            
        }
        
        // no array
        if ( !$array OR !is_array($array) ) {
            $array = $_REQUEST;
        }
    
        // check 
        if ( !array_key_exists($key,$array) OR $array[$key] == "" OR $array[$key] == 'false' ) {
            return $default;
        }
        
        // filter ?
        if ( $filter ) {
            $array[$key] = preg_replace("/[^".$filter."]+/","",$array[$key]);
        }
    
        // reutnr
        return $array[$key];
    
    }

}


//////////////////////////////////////////////////////////////////////
/// @breif Return object for a BitsyBox Module
/// @class Services_BitsyBox_Module
//////////////////////////////////////////////////////////////////////
class Services_BitsyBox_Module {
    
    // private
    private $itemsById = array();
    private $itemsByKey = array();
    private $items = false;
    private $bb = false;
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Construct a Services_BitsyBox_Module object
	///
	/// @param $raw raw module output (array)
	/// @param $bb referance to Services_BitsyBox object
	//////////////////////////////////////////////////////////////////////
    function __construct($raw,$bb) {
    
        // set raw data 
        $this->raw = $raw;
        $this->bb = $bb;
	
        // set id and key
        $this->id = $raw['@']['id'];
        $this->key = $raw['@']['key'];
        
        // no more
        unset($this->raw['@']);
        
        // set type
        $this->type = $raw['type'];
	
		if ( isset($this->raw['items']['@']) ) {
             
	     	// set some stuff
	     	$this->numOfPages = (int)$this->raw['items']['@']['pages'];
	     	$this->numOfItems = (int)$this->raw['items']['@']['items'];
	     	$this->numPerPage = (int)$this->raw['items']['@']['per'];
	     	$this->onPage = (int)$this->raw['items']['@']['page'];
	     	
	     	// no need for @ in items
	     	unset($this->raw['items']['@']);
	     	
     	}
        
    }

    
	//////////////////////////////////////////////////////////////////////
	/// @breif Return a module attribute
	///
	/// @param $name name of attribute
	/// @return attribute (string)
	//////////////////////////////////////////////////////////////////////
    function getAttribute($name) {
        if ( array_key_exists($name,$this->raw) ) {
            return $raw['$name'];
        }
        else {
            return false;
        }
    }    
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Get all items for the module
	///
	/// @return array of Services_BistyBox_Item objects
	//////////////////////////////////////////////////////////////////////
    function getItems() {
    
        // check
        if ( $this->items ) {
            return $this->items;
        }
        
        // hold
        $this->items = array();    
                
        // each 
        foreach ( $this->raw['items'] as $k => $i ) {
						
			// item
			$item = new Services_BistyBox_Item($i,$this->bb);
			
			// by id
			$this->items[] = $this->itemsById[(string)$i['@']['id']] = $this->itemsByKey[(string)$i['@']['key']] = $item;        
			
        }

        // give back
        return $this->items;
        
    }    

	//////////////////////////////////////////////////////////////////////
	/// @breif Get the first item of the module
	///
	/// @return Services_BistyBox_Item object
	//////////////////////////////////////////////////////////////////////
	function getFirstItem() {
		$this->getItems();
		return $this->items[0];
	}
	
	
	//////////////////////////////////////////////////////////////////////
	/// @breif Get the item at a given index
	///
	/// @param $idx index position
	/// @return Services_BistyBox_Item object
	//////////////////////////////////////////////////////////////////////
	function getItemAtIndex($idx) {
		$this->getItems();
		if ($idx < count($this->items) ) {
			return $this->items[$idx];
		}
		else {
			return false;
		}
	}

    
	//////////////////////////////////////////////////////////////////////
	/// @breif Get the item with the given Id
	///
	/// @param $id Item Id
	/// @return Services_BistyBox_Item object
	//////////////////////////////////////////////////////////////////////
    function getItemById($id) {        
    
        // get items
        $this->getItems();
        
        // check
        if ( !array_key_exists($id,$this->itemsById) ) {
            return false;
        }

        // give back
        return $this->itemsById[$id];
    
    }
    
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Get the item with the given Key Id
	///
	/// @param $Key Key Id 
	/// @return Services_BistyBox_Item object
	//////////////////////////////////////////////////////////////////////
    function getItemByKey($key) {

        // get items
        $this->getItems();
        
        // check
        if ( !array_key_exists($key,$this->itemsByKey) ) {
            return false;
        }

        // give back
        return $this->itemsByKey[$key];

    }
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Is the current module page the last page
	///
	/// @return bool
	//////////////////////////////////////////////////////////////////////
	public function isLastPage() {
		return ( $this->numOfPages == $this->onPage ? true : false );
	}
	
	//////////////////////////////////////////////////////////////////////
	/// @breif Is the current module page the first page
	///
	/// @return bool
	//////////////////////////////////////////////////////////////////////	
	public function isFirstPage() {
		return ( $this->onPage == 1 ? true : false );
	}
	
	//////////////////////////////////////////////////////////////////////
	/// @breif Get the naxt page
	///
	/// @return next page number
	//////////////////////////////////////////////////////////////////////
	public function nextPage() {
		if ( $this->isLastPage() ) {
			return false;
		}
		else {
			return $this->onPage+1;
		}
	}

	//////////////////////////////////////////////////////////////////////
	/// @breif Get the previous page
	///
	/// @return previous page number
	//////////////////////////////////////////////////////////////////////
	public function previousPage() {
		if ( $this->isFirstPage() ) {
			return false;
		}
		else {
			return $this->onPage-1;
		}
	}

	//////////////////////////////////////////////////////////////////////
	/// @breif Get a give field for a given item 
	///
	/// @param $name Name of the Field
	/// @param $idx Index position
	/// @return Services_BistyBox_Field object
	//////////////////////////////////////////////////////////////////////
	public function getField($name,$idx=0) {
	
		// get the itme
		$item = $this->getItemAtIndex($idx);
	
		// return
		if ( $item ) {
			return $item->getField($name);
		}
		else {
			return false;
		}
	
	}

}


//////////////////////////////////////////////////////////////////////
/// @breif Return object for a BitsyBox Item
/// @class Services_BistyBox_Item
//////////////////////////////////////////////////////////////////////
class Services_BistyBox_Item {

	// private
	private $bb;

	//////////////////////////////////////////////////////////////////////
	/// @breif Construct a Services_BitsyBox_Module object
	///
	/// @param $raw raw module output (array)
	/// @param $bb referance to Services_BitsyBox object
	//////////////////////////////////////////////////////////////////////
    function __construct($raw,$bb) {       
        
        // raw 
        $this->raw = $raw;
        $this->bb = $bb;
        
        // id and key 
        $this->id = $raw['@']['id'];
        $this->key = $raw['@']['key'];
        
        // unset
        unset($this->raw['@']);
        
    }
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Get a list of fields for the item
	///
	/// @param $types if given returns only fields of the given type (default:all)
	/// @return array of Services_BistyBox_Field objects
	//////////////////////////////////////////////////////////////////////
    function getFields($types=false) {
        
        // holder
        $fields = array();
		        
        // loop for fields
        foreach ( $this->raw as $name => $value ) {	
        
            // by type 
            if ( !$types OR ( $types AND in_array((string)$value['type'],$types) ) ) {
                $fields[$name] = $this->_initByType($name,$value);
            }
                
        }
        
        // return fields
        return $fields;
        
    }
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Get a given field
	///
	/// @param $name name of field
	/// @param $value return the value of a field instead of a Services_BistyBox_Field object (default:false)
	/// @return 
	///   if $value=false -- array of Services_BistyBox_Field objects
	///   if $value=true -- value of the field
	//////////////////////////////////////////////////////////////////////
    function getField($name,$value=false) {
    
        // need to get all fields
        $fields = $this->getFields();
        
        // bad field
        if ( !array_key_exists($name,$fields) ) {
        //	return false;
        }
        
        // return it
        if ( $value ) {
        	return $fields[$name]['value'];
        }
        else {
	        return $fields[$name];
		}
        
    }
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Init a field by it's type
	/// @private
	/// 
	/// @param $n field node
	/// @param $i item node
    /// @return return object
	//////////////////////////////////////////////////////////////////////
    private function _initByType($n,$i) { 
        if ( $i['type'] == 'list' OR $i['type'] == 'container' ) {
             $r = new Services_BitsyBox_Module($i['value'],$this->bb);
        }
        else {
            $r = new Services_BistyBox_Field($n,$i,$this->bb);
        }    
        return $r;
    }    
    
}



//////////////////////////////////////////////////////////////////////
/// @breif Return object for a BitsyBox Field
/// @class Services_BistyBox_Field
//////////////////////////////////////////////////////////////////////
class Services_BistyBox_Field {

	// private
	private $bb;
	
	// public
	public $name;		/// name of the field
	public $value;		/// value of the field
	public $type;		/// type of field

	//////////////////////////////////////////////////////////////////////
	/// @breif Construct a Services_BistyBox_Field object
	///
	/// @param $raw raw field output (array)
	/// @param $bb referance to Services_BitsyBox object
	//////////////////////////////////////////////////////////////////////
    function __construct($name,$raw,$bb) {
    
    	$this->bb = $bb;
        
        // set some stuff
        $this->raw = $raw;
        $this->name = $name;        
        $this->value = $raw['value'];
        $this->type = $raw['type'];
        $this->error = false;
        
    }
    
	//////////////////////////////////////////////////////////////////////
	/// @breif Resize an image field
	///
	/// @param $x desired image width
	/// @param $y desired image height 
	/// @return new image URL
	//////////////////////////////////////////////////////////////////////
	function resize($x,$y) {
		
		// make sure it's an image
		if ( $this->type != 'image' ) {
			$this->error = "This field is not an image";
			return false;
		}
		
		//pre-cdn migration url, just return old way
		if (stripos($this->value,'uploads/') === 0) {
			return (string)'/bitsy-image/img-resize/'.$x.','.$y.'/'.$this->value;
		}
		
		// cahce id
		$cid = '__images_'.$this->bb->key;
	
		// cache 
		$cache = false;
	
		// get the cache 
		if ( $this->bb->cache ) {
			$cache = $this->bb->cache->get($cid);
		}
		
			// no cahce
			if ( !$cache OR !is_array($cache) ) {
				$cache = array();
			}
			
		// fiel
		$file = $this->value;
		
		// image id
		$id = md5("{$file}.$x.$y");
		
		// check 
		if ( array_key_exists($id,$cache) ) {
			return (string)$cache[$id];
		}
		
		// remove cdn/uploads from url
		$file = str_replace("http://cdn.bitsybox.com/","",$file);
				
		// nope we need to request it
		$r = $this->bb->sendRequest("images/resize/{$file}",array('x'=>$x,'y'=>$y));
	
			// what up 
			if ( !$r ) {
				$this->error = $this->bb->error;
				return false;
			}
	
		// good
		$url = (string)$r['image']['url'];
		
		// save in the cache 
		$cache[$id] = $url;
		
			// save the cahce 
			if ( $this->bb->cache ) {
				$this->bb->cache->set($cid, $cache,(60*60*365));
			}
		
		// return 
		return $url;
	
	}
    
}

//////////////////////////////////////////////////////////////////////
/// @breif Interface for BitsyBox Caching Classes
/// @class iServices_BistyBox_Caching
//////////////////////////////////////////////////////////////////////
interface iServices_BistyBox_Caching {

	//////////////////////////////////////////////////////////////////////
	/// @breif Construct the class
	///
	/// @param $args Caching arguments
	//////////////////////////////////////////////////////////////////////
	public function __construct($args);

	//////////////////////////////////////////////////////////////////////
	/// @breif Get a cache object
	///
	/// @param $name name of cache object
	/// @return cache object on success / false on failure 
	//////////////////////////////////////////////////////////////////////
	public function get($name);

	//////////////////////////////////////////////////////////////////////
	/// @breif Set a cache object
	///
	/// @param $name name of cache object
	/// @param $data cache object
	/// @param $ttl time for cache object to live (in seconds)
	/// @return result of set oporation (bool)
	//////////////////////////////////////////////////////////////////////
	public function set($name,$data,$ttl=-1);
	
	//////////////////////////////////////////////////////////////////////
	/// @breif Delete a single cache object
	///
	/// @param $name name of cache file
	/// @param result of delete oporation (bool)
	//////////////////////////////////////////////////////////////////////	
	public function delete($name);
	
	//////////////////////////////////////////////////////////////////////
	/// @breif Dump all cache objects
	///
	/// @return result of dump oporation (bool
	//////////////////////////////////////////////////////////////////////	
	public function dump();
	
}

//////////////////////////////////////////////////////////////////////
/// @breif File Caching class for Services_BitsyBox
/// @class Services_BitsyBox_CacheFile
/// @implements iServices_BistyBox_Caching
//////////////////////////////////////////////////////////////////////
class Services_BitsyBox_CacheFile implements iServices_BistyBox_Caching {

	private $dir = "/tmp/bitsybox/";
	private $ttl = 300;

	//////////////////////////////////////////////////////////////////////
	/// @breif Construct the class
	///
	/// @param $args Caching arguments
	//////////////////////////////////////////////////////////////////////
	public function __construct($args) {
	
		// did they give a new dir
		if ( isset($args['dir']) ) {
			$this->dir = $args['dir'];
		}
		
		// did they give a custom ttl
		if ( isset($args['ttl']) ) {
			$this->ttl = $args['ttl'];
		}
	
		// make sure the dir exists
		if ( !file_exists($this->dir) ) {
			mkdir($this->dir,fileperms("/tmp/"),true);
		}
	
	}

	//////////////////////////////////////////////////////////////////////
	/// @breif Get a cache file
	///
	/// @param $name name of cache file
	///  @return cache file on success / false on failure
	//////////////////////////////////////////////////////////////////////
	public function get($name) {
	
        // file
        $file = $this->dir.$name;
                
        // does it exists 
        if ( !file_exists($file) ) {
            return false;
        }
    
        // get the file 
        $f = include($file);
    
        // check expire
        if ( $f['exp'] < time() ) {
            return false;
        }
    
        // return the unserizlied object
        $r = @unserialize(base64_decode($f['obj']));
        
        // return results
        return $r;	
	
	}
	

	//////////////////////////////////////////////////////////////////////
	/// @breif Set a cache file
	///
	/// @param $name name of cache file
	/// @param $data data to be cached 
	/// @param $ttl number of seconds to cache file
	/// @return result of set
	//////////////////////////////////////////////////////////////////////
	public function set($name,$data,$ttl=-1) {
	
		// if ttl is -1 we take the global val
		if ( $ttl == -1 ) {
			$ttl = $this->ttl;
		}

        // make our ttl
        $exp = time() * $ttl;
    
        // name our file 
        $file = $this->dir . $name;
    
        // make the file 
        $d = '<?php return array( "exp" => "'.$exp.'", "obj" => "'.base64_encode(serialize($data)).'"); ?>';
    
        // save 
        return file_put_contents($file,$d);
		
	}

	//////////////////////////////////////////////////////////////////////
	/// @breif Delete a cache file
	///
	/// @param $name name of cache file
	///  @return result of delete
	//////////////////////////////////////////////////////////////////////
	public function delete($name) {
	
        // file
        $file = $this->dir.$id;
        
        // unlink
        return unlink($file);	
		
	}

	//////////////////////////////////////////////////////////////////////
	/// @breif Delete all cache file
	//////////////////////////////////////////////////////////////////////	
	public function dump() {
	
		$d = dir($this->rir); 
		while($entry = $d->read()) { 
			if ($entry!= "." && $entry!= "..") { 
		 		@unlink($this->dir.$entry); 
		 	} 
		}             	
	
	}

}


?>