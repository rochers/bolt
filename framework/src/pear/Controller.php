<?php

class Controller {

	static $modules = array();
	static $embeds = array( 'js' => array() );
	static $id = 1;
	
	static function includeModule($name,$type='modules') {
		
		// modules root
		$root = Config::get('paths/'.$type);
		$path = false;
		$bolt = false;
		
		//echo "/home/bolt/share/pear/bolt/$type/$name/$name.render.php"; die;
		//echo "$root/$name/$name.render.php"; die;
		
		// does it exist
		if ( file_exists("$root/$name/$name.render.php") ) {
			$path = "$root/$name/$name.render.php";
		}
		else if ( file_exists("/home/bolt/share/pear/bolt/$type/$name/$name.render.php") ) {
			$path = "/home/bolt/share/pear/bolt/$type/$name/$name.render.php";
			$bolt = true;
		}

		
		// if path
		if ( $path ) {
			include_once($path);
		}
		
		// return
		return array($path,$bolt);
		
	}
	
	
	static function route($page) {
		
		// check for ajax request
		if ( $page == 'ajax' ) {
			self::ajax();
		}
		else if ( $page == 'xhr' ) {
			self::xhr();
		}
		else { 
			self::html($page);
		}
 	
	}
	
	static function xhr() {

        // try to figure out the 
        // path of the template 
        $base = Config::get('paths/page_templates',true);

    	// include the page
	    $pg = self::initModule(array('class'=>p('template')),array('parent'=>array(),'template'=>array()),'pages');
                                                        
        // now make sure the page exists
        if ( !$pg ) {     
           show_404();
        }       
        
        $r = $pg->render( array() );
        
        // parse tmpl
        list($html,$js) = self::parseHtmlForScriptTags($r->html);
        
        // output 
        header("Content-Type:text/javascript");
        
        // print 
        exit( json_encode( array("stat" => 1, 'html' => $html, 'bootstrap' => array( 'c' => @$page->args['bodyClass'], 'js' => $js ) ) ) );

	}
	
	static function ajax() {
	
	    // module
	    $module = p('module');
	    $origin = p('origin');
	    $type = p('type','modules');
	            
	    // set it 
	    $mod = self::initModule(array('class'=>$module),array(),$type);                                                      
	
	            // check the module
	            if ( !$mod ) {
	                    show_404();
	            }       
	
	    // call xhr
	    $resp = $mod->ajax();
	    
	    // check what to 
	    if ( p('xhr') !== 'true' ) {
	            exit(header("Location:".$origin));
	    }
	    else {
	    
	            // header
	            header("Content-Type:text/javascript");
	            
	            // prit 
	            exit( json_encode( array_merge(array('stat'=>1),$resp) ) );
	    
	    }
	
	}
 
	static function html($page,$project=false) {	
		
		// try to figure out the 
		// path of the template 
		$base = Config::get('paths/pages',true);		
		
		// if there's a project 
		// we need to add it's folder
		if ( $project ) {
			$base .= "{$project}/";
		}
				
        // include the page
        $pg = self::initModule(array('class'=>$page),array('parent'=>array(),'template'=>array()),'pages');
        			        			
    		// now make sure the page exists
    		if ( !$pg ) {     
    			show_404();
    		}               
        
        // render
		// html
		$o = $pg->render( array() );
		
            // no object something is wrong
            // so lets fake one
            if ( !is_object($o) ) {
                $o = new StdClass();
                $o->html = "";
                $o->args = array();
            }
            
		$args = $o->args;
			
		// body
		$args['_body'] = $o->html;
		
		// add settings
		$args['_args'] = $o->args;
		
		// render the template 
		$global = self::renderTemplate( Config::get('site/globalTemplate'), $args, $base );	
										
		// added embed lists
		$args['cssEmbedList'] = Controller::printEmbedList('css',true);
		$args['jsEmbedList'] = Controller::printEmbedList('js',true);
					
		// since this is special, we need to do it 
		// ourself
		$global = self::replaceTokens($global->html,$args);
	
		// now replace the body
		exit( $global ); 
	
	} 
	
	public static function renderPage($file,$p_args=array()) {

	
		return self::renderTemplate(
			$file,
			$p_args,
			Config::get('paths/pages')
		);
	}
	
	public static function renderModule($file,$p_args=array(),$base=false) {
		return self::renderTemplate($file,$p_args,$base);
	}

	public static function renderTemplate($file,$p_args=array(),$base=false) {
	
		// try to figure out the 
		// path of the template 
		if ( !$base ) {
			$base = Config::get('paths/modules',true);		
		}
		
		$base = "/" . trim($base,'/') . "/";
		
		// check for page
		if ( strpos($file,'.php') === false ) {
			$file .= ".template.php";
		}
	
		// start ob buffer 
		ob_start();
		
			// define all
			foreach ( $p_args as $k => $v ) {
				$$k = $v;
			}
		
			// include the page
			include($base.$file);
		
		// stop
		$page = ob_get_contents();
		
		// clean
		ob_clean();	
		
		// give it back
		return self::renderString($page,$p_args,$base);
	
	}
	
	public static function renderString($str,$p_args=array(),$base=false) {
	
	
		// parse the template
		list($template,$modules,$tmpl_settings) = self::parseTemplate($str);	
		
		// now we loop through each
		// one of the modules and create it 
		foreach ( $modules as $i => $m ) {
							
			// set it 
			$mod = self::initModule($m,array('parent'=>$p_args));	
								
				// no mod continue
				if ( !$mod ) { continue; }
							
			// html
			$o = $mod->render($m['cfg']);
			
			if ( $o ) {
			
				// render
				$template = str_replace("<module{$m['id']}>",$o->html,$template);
				
				// add to list
				$modules[$i]['html'] = $o->html;
			}
			
			$modules[$i]['ref'] = $mod;
	
		}
		
		// object
		$o = new StdClass();
		
		// set some stuff
		$o->html = $template;
		$o->modules = $modules;
		$o->args = $p_args;
	
		// give it back
		return $o;
	
	}	
	
	public function replaceTokens($template,$args) {

		foreach ( $args as $k => $v ) {
			if ( is_array($v) ) { 
				foreach ( $v as $kk => $vv ) {
					if ( is_string($vv) ) {
						$template = str_replace('{$'.$k.'['.$kk.']}',$vv,$template);					
					}
				}
			}
			else if ( is_string($v) ) {
				$template = str_replace('{$'.$k.'}',$v,$template);
			}
		}
		
				
		$template = preg_replace('/\{\$([a-zA-Z0-9]+)\}/',"",$template);	
				
		// re
		return $template;

	}
	
	public function initModule($mod,$args,$type='modules') {		
				
		// include
		list($_m,$bolt) = self::includeModule($mod['class'],$type);
		
			// nope		
			if ( !$_m ) {
				return;
			}
			
		// if bolt we need to namespace
		if ( $bolt == true ) {
			$mod['class'] = '\bolt\\'.$mod['class'];
		}
			
		// go for it 
		$m = new $mod['class']($args);			
		
			// check for embeds
			if ( property_exists($m,'embeds') ) {
				
				// get htem
				$embeds = $m::$embeds;
				
				// if js
				if ( isset($embeds['js']) ) {
					foreach ( $embeds['js'] as $name => $file ) {
						self::addEmbed('js',$name,$file);
					}
				}
				
			}			
		
		// save
		self::$modules[$mod['class']] = $m;
		
		// give back
		return $m;
		
	}

	public function parseTemplate($string) {
	
		// modules
		$modules = array();
		$settings = array();
	
		// lets start parsing
		if ( preg_match_all("/\{%\s?([a-z\_]+)(\([^\)]+\))?\s?%\}/",$string,$match,PREG_SET_ORDER) ) {
									
			// loop through each module and 
			// figure out what is there
			foreach ( $match as $m ) {
				if ( substr($m[0],1) != '$' ) {
				
					$i = self::$id++;
				
					// add the module
					$mod = array( 'id' => $i, 'class' => trim($m[1]), 'cfg' => array() );
					
						// isset
						if ( isset($m[2]) ) {
							// check for base64
							if ( stripos($m[2],'base64:') !== false ) {
								$mod['cfg'] = unserialize(
									base64_decode(
										trim(
											str_replace("base64:","",$m[2])
										,'()')
									)
								);
							}
							else {
								$mod['cfg'] = array();
								$params = explode(",",trim($m[2],'()'));								
								foreach ( $params as $p ) {
									list($k,$v) = explode(':',$p);
									$mod['cfg'][$k] = $v;
								}
							}
						}
						
					// modules
					$modules[] = $mod;
				
					// replace
					$string = preg_replace("/".preg_quote($m[0],'/')."/","<module{$i}>",$string,1);
					
				}							
			}
		
		}
		
		// lets start parsing
		if ( preg_match_all("/\{\#([a-zA-Z\_]+)\:\s?([^\#]+)\#\}/",$string,$match,PREG_SET_ORDER) ) {
			foreach ( $match as $m ) {
				$settings[trim($m[1])] = trim($m[2]); 
				$string = str_replace($m[0],"",$string);
			}
		}		
	
		// just give back
		return array($string,$modules,$settings);
	
	}
	
	
	public static function parseHtmlForScriptTags($body) {
	
		// need to remove comments 
		$body = preg_replace(array("/\/\/[a-zA-Z0-9\s\&\?\.]+\n/","/\/\*(.*)\*\//")," ",$body);
		
		// javascript 		
		$jsInPage = preg_match_all("/((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))/i",$body,$js);		
		
	
			// if yes remove 
			if ( $jsInPage ) {
				$body = preg_replace("/((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))/i","",$body);
			}	
		
		// give back
		return array($body,@$js[3]);
	
	}
	
	public static function addEmbed($type,$name,$project,$file) {
		self::$embeds[$type][$name] = array('file' => Config::asset($type,$file,$project) );
	}


	public static function printEmbedList($type='js',$return=false) {
		$r = false;
		if ( $type == 'js' AND isset(self::$embeds['js']) ) {		
			$r = json_encode(self::$embeds['js']);
		}	
		else if ( $type == 'css' AND isset(self::$embeds['css']) ) {
		  $l = array();
		  
		  foreach ( self::$embeds['css'] as $css ) {
		      $l[] = $css['file'];
		  }
		
		 $r = implode(",",$l);
		}
		
		if ( $return ) {
			return $r;
		}
		else {
			echo $r;
		}
		
	}

	public static function printCfg($a,$rtn=false) {
		$c = "base64:".base64_encode(serialize($a));
		if ( $rtn )  {
			return $c;
		}
		else {
			echo $c;
		}
	}

    public static function printAssets($csv,$type='css') { 
        
        switch($type) { 
        	
        	case 'css':
        		$header = 'Content-Type: text/css';
        		break;
        	
        	case 'js':
        		$header = 'Content-Type: application/x-javascript';
        		break;
        		
        	case 'jpg':
        		$header = 'Content-Type: image/jpg';
        		break;
        	
        	case 'png':
        		$header = 'Content-Type: image/png';
        		break;
        		
        	case 'gif':
        		$header = 'Content-Type: image/gif';
        		break;
        
        }
        
        header($header);        
        
        // split out 
        $files = explode(",",$csv);
        
        //var_dump($csv); die;

        // blocks
        $blocks = array();
        
        $count = 0;
		
		// url
        $url = Config::get("paths/asset_url"); 
		
        // loop me 
        foreach ( $files as $f ) { 
                	        	
            if ( strpos($f,'http') !== false ) {
                $blocks[$count] = `curl '$f'`;
            }
            else {
                
                $blocks[$count] = file_get_contents( Config::get('paths/asset_root') . trim($f,'/') );
                
                if ($type == 'css') {
                	
                	// if bolt use the bolt path to images
                	if (strpos($f,'bolt/css') !== false) { 
                		$url = '/assets/bolt/';
                		$blocks[$count] = preg_replace("#../images#",$url."images",$blocks[$count]);
                	} else { 
                		$blocks[$count] = preg_replace("#../images#",$url."images",$blocks[$count]);
                	}
                	
                }
                
            }
            
            $count++;
            
        }
        
        // blocks
        $css = implode("\n\n",$blocks);
        
        // replace with asset url
        exit( $css );
    
    }

}


?>