<?php

	///////////////////////////////////
	/// AUTOLOAD
	///////////////////////////////////	
	function __autoload($class) { 
	
		// we only want the last part of the class
		if ( strpos($class,'\\') !== false ) {
			$class = array_pop( explode('\\',$class) );
		}
	
		// check for autoload in global
		if ( !isset($GLOBALS['_auto_loader']) ) {
			return;
		}
		
		// try to find it
		foreach ( $GLOBALS['_auto_loader'] as $path ) {
				
			// if we should convert _ to /
			if ( isset($path[2]) AND $path[2] == true ) {
				$class = str_replace("_","/",$class);
			}
		
			// file name
			$file = $path[1].$class.$path[0];		
		
			// does it exist
			if ( file_exists($file) ) {
				require_once($file); return;
			} 
			else if ( file_exists( strtolower($file) ) ) {
				require_once(strtolower($file)); return;
			}
			
		}
	
	}
	
	// require
	require_once(FRAMEWORK."Database.php");

	// define the bolt module template
	define("BOLT_MODULES","/home/bolt/share/pear/bolt/modules/");

	// dev
	if ( defined('DEV') AND DEV === true ) {
	    error_reporting(E_ALL^E_DEPRECATED);
	    ini_set("display_errors",1);		
	}
	
	// set 
    date_default_timezone_set((defined('DEFAULT_TZ') ? DEFAULT_TZ : "UTC"));      

	// local
	setlocale(LC_MONETARY, 'en_US');

    // get the file name
    $path = explode("/",$_SERVER['SCRIPT_FILENAME']);

    // need to get base tree
    $uri = explode('/',$_SERVER['SCRIPT_NAME']);  

    // define 
    if ( isset($_SERVER['HTTP_HOST']) ) {
	    
	    define("HTTP_HOST",		 $_SERVER['HTTP_HOST']);
	    define("HOST",      	 ($_SERVER['SERVER_PORT']==443?"https://":"http://").$_SERVER['HTTP_HOST']);
	    define("HOST_NSSL",  	 "http://".$_SERVER['HTTP_HOST']);
	    define("HOST_SSL",     	 "https://".$_SERVER['HTTP_HOST']);
	    define("URI",      		 HOST.implode("/",array_slice($uri,0,-1))."/");
	    define("URI_NSSL", 		 HOST_NSSL.implode("/",array_slice($uri,0,-1))."/");
	    define("URI_SSL",  		 HOST_SSL.implode("/",array_slice($uri,0,-1))."/");
	    define("COOKIE_DOMAIN",	 false);
	    define("IP",			 $_SERVER['REMOTE_ADDR']);
	    define("SELF",			 HOST.$_SERVER['REQUEST_URI']);    
	    
	}

	// helpdes
	define("HOUR",(60*60));
	define("DAY",(60*60*24));
	define("DATE_LONG_FRM", "l, F jS, Y \n h:i:s A");
	define("DATE_SHORT_FRM", "F jS, Y \n h:i:s A");	
	define("DATE_ONLY", "l, F jS, Y");	
	define("TIME_FRM", "h:i:s A");	

	////////////////////////////////
	///  @breif config
	////////////////////////////////
	class Config {	
	
		// config
		private static $config = array(
            'db' => array(),
			'urls' => array(),
			'paths' => array(),
		);	
		
		////////////////////////////////
		/// @breif get a predefined config
		////////////////////////////////		
		public static function get($var,$isPath=false) {
			
			// config
			$config = self::$config;
			
				// check for a sub
				if ( strpos($var,'/') !== false ) {
					list($ary,$var) = explode('/',$var);
					$config = $config[$ary];
				}			

			// what evn
			$var_pf = $var . (DEV?'_dev':'_prod');
			
			// val
			$val = false;
			
			// var
			if ( isset($config[$var_pf]) ) {
				$val = $config[$var_pf];
			}
			else if ( isset($config[$var]) ) {
				$val = $config[$var];
			}
			
			return ( $isPath ? "/".trim($val,"/")."/" : $val );
			
		}
	
		////////////////////////////////
		/// @breif set a config val
		////////////////////////////////
		public static function set($var,$val) {
			self::$config[$var] = $val;		
		}
					
		public static function asset($type,$file,$project='global') {
			if (stripos($file,'http') === 0) { 
				return $file;
			} else { 
				return "/assets/{$project}/{$type}/{$file}";
			}
		}
	
		////////////////////////////////
		/// @breif get a url
		////////////////////////////////		
		public static function url($key,$data=false,$params=false,$uri=URI) {
			
			// key = 'slef'
			if ( $key == 'self' ) {
				return SELF;
			}
			
			// define our urls
			$pages = self::$config['urls'];
			
			// get a url
			if ( array_key_exists($key,$pages) ) {
				$url = $pages[$key]; 
			}
			else {
				$url = $key;
			}
			
			
			// repace toeksn
			if ( is_array($data) ) {
							
				foreach ( $data as $k => $v ) {
					if ( !is_array($k) AND !is_array($v) ) {
				    			
				        // orig 
				        $orig = $v;		
				    				        
						// check for * in key
						if ( substr($k,0,1) != '*' ) {
							$v = strtolower(preg_replace(
								array("/[^a-zA-Z0-9\-\/]+/","/-+/"),
								"-",
								html_entity_decode($v,ENT_QUOTES,'utf-8')
							));						
						}
						else {
							$k = substr($k,1);
						}
						
						// url
						$url = str_replace('{*'.$k.'}',$orig,$url);
						$url = str_replace('{'.$k.'}',trim($v,'-'),$url);
						
					}
					else if ( is_array($v) ) {
                        
                        foreach ( $v as $kk => $vv ) {
                            if ( is_string($vv)) {
                                $url = str_replace('{*'.$k.'['.$kk.']}',$vv,$url);                            
                                $url = str_replace('{'.$k.'['.$kk.']}',$vv,$url);
                            }
                        }
					
					}
				}
			}
			
			// clean up
			$url = preg_replace("/\{\*?[a-z\[\]]+\}\/?/","",$url);
			
			// params
			if ( is_array($params) ) {
				$p = array();
				foreach ( $params as $k => $v ) {
					$p[] = "{$k}=".urlencode($v);
				}
				$url .= (strpos($url,'?')==false?'?':'&').implode('&',$p);
			}
			
			// give back
			if (stripos($url,"http://") === 0) { 
				return $url;
			} else { 
				return $uri . $url;
			}
		
		}
		
		static function addUrlParams($url,$params) {
		
			// parse the url
			$u = parse_url($url);
		
			// loop and add to params
			if ( isset($u['query']) ) {
				foreach ( explode('&',$u['query']) as $i ) {
					if ( $i ) {
						list($k,$v) = explode('=',$i);
						if ( !array_key_exists($k,$params) ) {
							$params[$k] = $v;
						}
					}
				}
			}
			
			// reconstruct
			$url = $u['scheme']."://".$u['host'].(isset($u['port'])?":{$u['port']}":"").$u['path'];
		
			$p = array();
			foreach ( $params as $k => $v ) {
				$p[] = "{$k}=".urlencode($v);
			}
			$url .= (strpos($url,'?')==false?'?':'&').implode('&',$p);		
			
			if ( isset($u['fragment']) ) {
				$url .= $u['fragment'];
			}
			
			return $url;
		
		}
	
	
	}


	/**
	 * global paramater function
	 * @method	p
	 * @param	{string}	key name
	 * @param	{string} 	default value if key != exist [Default: false]
	 * @param	{array}		array to look in [Default: $_REQUEST]
	 * @param   {string}    string to filter on the return
	 */
	function p($key,$default=false,$array=false,$filter=FILTER_SANITIZE_STRING) {
	
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
		if ( $array === false ) {
			$array = $_REQUEST;
		}
		
		// not an array
		if ( !is_array($array) ){ return false; }
	
		// check 
		if ( !array_key_exists($key,$array) OR $array[$key] == "" OR $array[$key] == 'false' ) {
			return $default;
		}	
		
		// if final is an array,
		// weand filter we need to filter each el		
		if ( is_array($array[$key]) ) {
			
			// filter
			array_walk($array[$key],function($item,$key,$a){
				$item = p($key,$a[1],$a[0]);
			},array($filter,$array[$key]));

		}
		else {
			$array[$key] = filter_var($array[$key],$filter);
		}
		
		// reutnr
		return $array[$key];
	
	}
	
		// p raw
		function p_raw($key,$default=false,$array=false) {
			return p($key,$default,$array,FILTER_UNSAFE_RAW);
		}
	
	/**
	 * global path function 
	 * @method	pp
	 * @param	{array}		position (index) in path array
	 * @param	{string}	default 
	 * @param	{string}	filter
	 * @return	{string}	value or false
	 */
	function pp($pos,$default=false,$filter=false) {
		
		// path 
		$path = explode('/',trim(p('path'),'/'));
		
		// yes?
		if ( count($path)-1 < $pos OR ( count($path)-1 >= $pos AND $path[$pos] == "" ) ) {
			return $default;
		}
	
		// filter
		if ( $filter ) {
			$path[$pos] = preg_replace("/[^".$filter."]+/","",$path[$pos]);
		}
		
		// give back
		return $path[$pos];
	
	}	

	function utctime() {
	
		// datetime
		$dt = new DateTime('now',new DateTimeZone('UTC'));		
		
		// return utctime
		return $dt->getTimestamp();
	
	}
	
	function plural($str,$count) {
		if ( is_array($count) ) { $count = count($count); }
		
		if ( substr($str,-1) == 'y' AND $count > 1 ) {
			return substr($str,0,-1)."ies";
		}
		return $str . ($count!=1?'s':'');
	}
	
	function ago($tm,$rcs = 0) {
	
	    $cur_tm = utctime(); $dif = $cur_tm-$tm;	
	
    	// check user for a tzoffset
        $u = Session::getUser();
        
        // offset
        if ( $u AND $u->profile_tzoffset ) {
        	$tm += $u->profile_tzoffset;
        	$cur_tm += $u->profile_tzoffset;
        }		
	
	    $pds = array('second','minute','hour','day','week','month','year','decade');
	    $lngh = array(1,60,3600,86400,604800,2630880,31570560,315705600);
	    for($v = sizeof($lngh)-1; ($v >= 0)&&(($no = $dif/$lngh[$v])<=1); $v--); if($v < 0) $v = 0; $_tm = $cur_tm-($dif%$lngh[$v]);
	   
	    $no = floor($no); if($no <> 1) $pds[$v] .='s'; $x=sprintf("%d %s ",$no,$pds[$v]);
	    return $x . ' ago';
	}
	
	function left($theTime)
		{
			$now = strtotime("now");
			$timeLeft = $theTime - $now;
			$theText = '';			
			 
			if($timeLeft > 0)
			{
			$days = floor($timeLeft/60/60/24);
			$hours = $timeLeft/60/60%24;
			$mins = $timeLeft/60%60;
			$secs = $timeLeft%60;
			
			// check for days
			if($days) {
					$theText .= $days . " day";
					
					if ($days > 1) { $theText .= 's'; }
						
			} 
			
			if ( $hours > 0 ) {
			
					$theText .= ' '.$hours . " hour";
				
					if ($hours > 1) { $theText .= 's'; }
			
			}
					
					
					$theText .= ' '.$mins . " min";
					
					if ($mins > 1) { $theText .= 's'; }		

			
					$theText .= ' '.$secs . " sec";
					
					if ($secs > 1) { $theText .= 's'; }

					
		}
		
		return $theText;
		
	}
	
	function short($str,$len=200,$onwords=true) {
		if ( mb_strlen($str) < $len ) { return $str; }
		if ( !$onwords ) {
			if ( mb_strlen($str) > $len ) {
				return substr($str,0,$len)."...";
			}
		}
		else {
			$words = explode(' ',$str); 
			$final = array();
			$c = 0;
			foreach ( $words as $word ) {
				if ( $c+mb_strlen($word) > $len ) {
					return implode(' ',$final). '...';
				}
				$c += mb_strlen($word);
				$final[] = $word;
			}
		}
	
		return $str;
		
	}
	
	function br2nl($string){
	$return=eregi_replace('<br[[:space:]]*/?'.
	'[[:space:]]*>',chr(13).chr(10),$string);
	return $return;
	} 	
	
	function show_404($page=false) {
		ob_clean();
		header("HTTP/1.1 404 Not Found",TRUE,404); 
		
		if (!file_exists($page)) {
			$page = '/home/bolt/share/pear/bolt/framework/404.php';
		} 
		
		include($page);
		
		die;
	}

	function factory($n,$ns='dao') {
		$class = '\\'.$ns.'\\'.$n;
		return new $class;
	}
	
?>