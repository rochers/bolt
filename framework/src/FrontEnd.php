<?php

// mail
include('Net/SMTP.php');
include('Mail.php');

/////////////////////////
/// Framework
/////////////////////////	
abstract class FrontEnd extends DatabaseMask  {
		
	// holders
	protected $db = false;
	protected $cache = false;
	protected $event = false;
	protected $tokens = false;
	protected $args = false;
	protected $aws = false;
	
	/* __construct */
	public function __construct($args=false) {		
	
		$this->args = $args;

		// database
		$this->db = Database::singleton();					
		$this->cache = Cache::singleton();
		$this->event = Events::singleton();
		
		// get the session
		if ( Config::get('site/no-session') !== true ) {
			$this->session = Session::singleton();
		}

		// s3 info is set?
		if ( Config::get('site/s3-key') ) {
		
	        // s3
	        $this->aws = new S3( Config::get('site/s3-key'), Config::get('site/s3-secret'));		
		
		}

		// form token
		$this->tokens = Config::get('token-key');

	}
	
	function __destruct() {
		
	}
	
	// url shortcut
	public function url($key,$data=false,$params=false) {
		return Config::url($key,$data,$params);
	}
	
	public function sendEmail($args) {
		return self::doSendEmail($args);
	}
	
	// send email
	public static function doSendEmail($args) {
	
		// check for from 
		if ( !isset($args['from']) ) {
			$args['from'] = "no-reply@dailyd.com";
		}

		// headers
		$headers['From']    = $args['from'];
		$headers['To']      = $args['to'];
		$headers['Subject'] = $args['subject'];
		
		// body
		$body = $args['message'];

		// params
		$params = array(
			'host'		=> 'smtp.gmail.com', 
			'port'		=> '587',
			'auth'		=> true,
			'username'	=> 'no-reply@dailyd.com',
			'password'	=> 'd@ilyD'
		);

		// Create the mail object using the Mail::factory method
		$mail =& Mail::factory('smtp', $params);

		// send it out
		return $mail->send($args['to'], $headers, $body);	
	
	}
		
	
	public function setCache($cid,$data,$ttl) {		
		if ( $this->mem ) {
			return $this->mem->set($cid,$data,MEMCACHE_COMPRESSED,$ttl);
		}
		return false;
	}

	public function getCache($cid) {
		if ( $this->mem ) {
			return $this->mem->get($cid);
		}
		return false;
	}			
	
	public function go($url) {
		self::location($url);
	}

	public static function location($url) {
		exit( header("Location:".$url) );
	}
	
	public function generateFormToken($name) {
	
		$tok = $this->randomString(10).base64_encode( $this->md5( time() ) .  rand(1,999) );
	
		// set it 
		$tokens = array( 'token' => $tok, 'expires' => utctime()+(60*60), 'ip' => IP );
	
		// delete old token
		$this->cache->delete("{$this->tokens}:{$name}",'tokens');
	
		// save
		$this->cache->set("{$this->tokens}:{$name}",$tokens,(60*60),'tokens');
		
		// return tok
		return $tok;
	
	}
	
	public function validateFormToken($name,$given) {
	
		// check for the token
		$tok = $this->cache->p_get("{$this->tokens}:{$name}",'tokens');
		
		// does it exist
		if ( !$tok ) { return false; }
	
		// remove the token
		$this->cache->delete("{$this->tokens}:{$name}",'tokens');	
	
		// valid
		if ( $tok['token'] == $given AND utctime() < $tok['expires'] AND $tok['ip'] == IP ) {					
			
			// yes
			return true;
			
		}
		
		// bad
		return false;
	
	}
	
	public function validateStr($str,$as,$return=false) {
	
		// what up
		if ( $as == 'hosturl' ) {
			
			// parse
			$host = parse_url($str,PHP_URL_HOST);
			$local = parse_url(HOST,PHP_URL_HOST);
			
			if ( $host == $local ) {
				return $str;
			}
			
		}
	
		// bad
		return $return;
	
	}
	
			
			/**
	Validate an email address.
	Provide email address (raw input)
	Returns true if the email address has the email 
	address format and the domain exists.
	*/
	function validateEmail($email)
	{
	   $isValid = true;
	   $atIndex = strrpos($email, "@");
	   if (is_bool($atIndex) && !$atIndex)
	   {
	      $isValid = false;
	   }
	   else
	   {
	      $domain = substr($email, $atIndex+1);
	      $local = substr($email, 0, $atIndex);
	      $localLen = strlen($local);
	      $domainLen = strlen($domain);
	      if ($localLen < 1 || $localLen > 64)
	      {
	         // local part length exceeded
	         $isValid = false;
	      }
	      else if ($domainLen < 1 || $domainLen > 255)
	      {
	         // domain part length exceeded
	         $isValid = false;
	      }
	      else if ($local[0] == '.' || $local[$localLen-1] == '.')
	      {
	         // local part starts or ends with '.'
	         $isValid = false;
	      }
	      else if (preg_match('/\\.\\./', $local))
	      {
	         // local part has two consecutive dots
	         $isValid = false;
	      }
	      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
	      {
	         // character not valid in domain part
	         $isValid = false;
	      }
	      else if (preg_match('/\\.\\./', $domain))
	      {
	         // domain part has two consecutive dots
	         $isValid = false;
	      }
	      else if
	(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
	                 str_replace("\\\\","",$local)))
	      {
	         // character not valid in local part unless 
	         // local part is quoted
	         if (!preg_match('/^"(\\\\"|[^"])+"$/',
	             str_replace("\\\\","",$local)))
	         {
	            $isValid = false;
	         }
	      }
	   }
	   return $isValid;
	}

    public function randomString($len=30) { 
    	return self::randStr($len);
    }	
	
    public static function randStr($len=30) {
        // chars
        $chars = array(
                'a','b','c','d','e','f','g','h','i','j','k','l','m','n','o','p','q','r','s','t','u','v','w','x','y','z',
                'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','V','T','V','U','V','W','X','Y','Z',
                '1','2','3','4','5','6','7','8','9','0'
        );
       
        // suffle
        shuffle($chars);
       
        // string
        $str = '';
       
        // do it
        for ( $i = 0; $i < $len; $i++ ) {
                $str .= $chars[array_rand($chars)];
        }
       
        return $str;   

	}		

   public function resizeImage($file, $w, $h, $crop=FALSE, $ty='jpg') {
   
		list($width, $height) = getimagesize($file);
		
		$r = $width / $height;
		
		if ($crop) {
			if ($width > $height) {
				$width = ceil($width-($width*($r-$w/$h)));
			} 
			else {
				$height = ceil($height-($height*($r-$w/$h)));
			}
		
			$newwidth = $w;
			$newheight = $h;
		} 
		else {
			if ($w/$h > $r) {
				$newwidth = $h*$r;
				$newheight = $h;
			} 
			else {
				$newheight = $w/$r;
				$newwidth = $w;
			}
		}
		
		
		$ext = array_pop( explode(".",$file) );
		
		$src = imagecreatefromstring( file_get_contents($file) );

		imagecolorallocate($src,255,255,255);		
		
		// dest image
		$dst = imagecreatetruecolor($newwidth, $newheight);

		$w = imagecolorallocate($dst,255,255,255);		
		
		imagefill($dst,0,0,$w);
		

		
		// resample
		imagecopyresampled($dst, $src,  0,  0,  0,  0, $newwidth, $newheight, $width, $height);
		
		imagedestroy($src);
		
		// new image
		$tmp = "/tmp/".uniqid() . time();
		
		// return the image
		switch($ty) {
			case 'jpg':
				imagejpeg($dst,$tmp,100); break;
			case 'png':
				imagepng($dst,$tmp); break;
			case 'gif':
				imagegif($dst,$tmp); break;		
			default:
				return false;
		};
		
		imagedestroy($dst);
		
		// tmp
		return $tmp;
		
	}

	public function printJsonResponse($rsp) {
		header("Content-Type: text/javascript");
		exit( json_encode( array_merge(array('stat'=>1),$rsp) ) );
	}
	
	public function makeReturnToken($args) {
		return base64_encode( json_encode($args) );
	}
	
	public function parseReturnToken($token) {
		return json_decode( base64_decode(urldecode($token)),true);
	}	
	
	public function getReturnToken($default=false) {	
		if ( !$default ) { $default = array('do'=>'redi','url'=>$this->url('home')); } 
		return json_decode( base64_decode( urldecode(p('return',$this->makeReturnToken($default)))),true);
	}		
	
	public function md5($str) {
		return self::getMd5($str);
	}	
	
	public static function getMd5($str) {
		return md5("jf89pohij2;3'damiufj".$str."84$89adfaw349408 43a4 038w4r awef aweufh7ao38rhuanwk/ mef");
	}
	
	
	public function thumb($str) {
		$x = array_pop(explode(".",$str));
		return str_replace(".{$x}","_thumb.{$x}",$str);
	}
	
	public function uuid($parts=4) {
		return self::getUuid($parts);
	}
	
	public static function getUuid($parts=4) {

			$uuid = implode('-',array_slice(explode('-',trim(`uuid`)),0,$parts));
		return $uuid;
	}
	
	
} // END framework






?>