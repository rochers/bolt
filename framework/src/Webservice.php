<?php

namespace dao;

/*! @class Webservice
    @abstract A class for retreiving data objects from web service APIs which serve XML or JSON format.
    @discussion The Webservice class supports the Framework Cache if desired, for caching of data objects.
*/

class Webservice { 

	// should debugging statements be printed?
	private $debug		= false;
	
	// The host to connect to
	private $host		= false;

	// the port to connect to
	private $port		= 80;

	// should be the literal strings http or https
	private $protocol	= 'http';

	// output that should be given by the xml-api
	private $output		= 'simplexml';

	// literal strings hash or password
	private $auth_type 	= false;

	//  the actual password or hash
	private $auth 		= false;
	
	// username to authenticate as
	private $user		= false;
	
	// The HTTP Client to use
	private $method		= 'curl';
	
	// The HTTP Client to use
	private $baseUrl	= false;
	
	public $cache = false;
	
	protected $private  = array();
	
	
	/*! @function __construct
	    @abstract creates a Webservice object based on array of config values
	    @param config - array of configuration values
	    @result Object - the Webservice object, ready to make calls
		or false if something goes wrong
 	*/
	public function __construct( $config = array() ) {
		
		// loop through config values and set ones that are allowed 
		foreach ($config as $k=>$v) {
						
			$this->$k = $v;
			
		}
		
		
		// set the baseUrl
		$this->baseUrl = $this->protocol . '://' . $this->host;
		
		$this->port != 80 ? $this->baseUrl .= ':' . $this->port . '/' : $this->baseUrl .= '/';
		
		$this->cache = \Cache::singleton();
					
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
            return $data[$name];										
		}

        // check for _
        else if ( mb_strpos($name,'_') ) {
            
            // explode out 
            list($ary,$key) = explode('_',$name);
            
            // what
            if ( array_key_exists($ary,$data) AND is_array($data[$ary]) AND array_key_exists($key,$data[$ary]) ) {
				return $data[$ary][$key];
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
	   
		if ( array_key_exists($name,$this->data) ) {
		 	$this->data[$name] = $val;
		} 
		
        // check for _
        else if ( mb_strpos($name,'_') ) {
            
            // explode out 
            list($ary,$key) = explode('_',$name);
            
            // what
            if ( array_key_exists($ary,$this->data) ) {
                $this->data[$ary][$key] = $val;
            }
            
        }

	}
	
	
	/*! @function sendRequest
	    @abstract sends a request to the API server
	    @param $uri module, method and path of the request
        @param $params query params for request
        @param $post post params for request
        @param $headers additional headers for the request
        @return 
 	*/
	public function sendRequest($uri,$params=array(),$post=false,$headers=array()) {
                              
        // url 
        $url = $this->baseUrl . $uri; 
                     
        // params
        $p = array();
    
            // add our params 
            foreach ( $params as $k => $v ) {
                if ( $k AND $v ) {
                    $p[] = $k."=".rawurlencode($v);
                }
            }

        // append
        if (!empty($p)) { 
        	$url .= '?'.implode('&',$p);
        }
        
        // new curl request
        $ch = curl_init();

        // set some stuff
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		
        // add headers
        //curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    
        // add params
        if ( $post ) {
	        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
        }
        
        // make the request
        $result = curl_exec($ch);    
                    
		// bad curl call
        if ( curl_getinfo($ch,CURLINFO_HTTP_CODE) != 200) {
        	
        	// show error
        	$msg = 'Webservice Error ' . curl_getinfo($ch,CURLINFO_HTTP_CODE) . ',' . $url;
        	
        	// error
        	$this->error = $msg;
        	
        	// log
			error_log($msg);				

        }    
        
        // close curl
        curl_close($ch);           
        
        // check result
        if (empty($result)) { 
        
        	$msg = 'Webservice call returned was empty: '. $url;
        	
        	error_log($msg);
        
        }        
                   
        // give back
        return $result;
    
    }
	

}