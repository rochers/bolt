<?php

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
	private $output		= 'json';

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
					
	}
	
	public function setHost($host) {
		$this->host = $host;
	}
	
	public function setPort($port) {
		$this->port = $port;
	}
	
	protected function getBaseUrl() {
		return  $this->protocol . '://' . $this->host . ":" . $this->port . "/";		
	}
		
	/*! @function sendRequest
	    @abstract sends a request to the API server
	    @param $uri module, method and path of the request
        @param $params query params for request
        @param $post post params for request
        @param $headers additional headers for the request
        @return 
 	*/
	public function sendRequest($uri,$params=array(),$method='GET',$headers=array()) {
                              
        // url 
        $url = $this->getBaseUrl() . ltrim($uri,'/');  
        
                     
        // params
        $p = array();
    
            // add our params 
            if ( $method == 'GET' ) {
	            foreach ( $params as $k => $v ) {
	                if ( $k AND $v ) {
	                    $p[] = $k."=".rawurlencode($v);
	                }
	            }
            }

        // append
        if (!empty($p)) { 
        	$url .= (strpos($uri,'?')===false?'?':':').implode('&',$p);
        }        
        
        // new curl request
        $ch = curl_init();

        // set some stuff
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_HEADER, 0);    
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,5);
		
        // add headers
        curl_setopt($ch,CURLOPT_HTTPHEADER,$headers);
    
        // add params
        if ( $method == 'POST' ) {
	        curl_setopt($ch,CURLOPT_POSTFIELDS, http_build_query($params) );
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
			
			// bad
			return false;

        }    
        
        // close curl
        curl_close($ch);           
        
        // check result
        if ( !$result ) { 
			return false;
        }        
                  
		// if json
		if ( $this->output == 'json' ) {
			$result = json_decode($result,true);
		}
		else if ( $this->output == 'xml' ) {
			$result = simplexml_load_string($result);
		}
                   
        // give back
        return $result;
    
    }
	

}