<?php

class Controller {

	static $modules = array();
	static $embeds = array( 'css' => array(), 'js' => array() );
	static $id = 1;
	static $globals = array();

	static function registerGlobal($key, $val)
	{
		self::$globals[$key] = $val;
	}

	static function includeModule($name, $type='modules')
	{

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
			include_once $path;
		}

		// return
		return array($path, $bolt);

	}


	static function route($page)
	{

		// check for ajax request
		if ( $page == 'ajax' ) {
			self::ajax();
		}
		else if ( $page == 'xhr' ) {
				self::xhr();
			}
		else if ( $page == 'rss' ) {
				self::rss();
			}
		else {
			self::html($page);
		}

	}

	static function xhr()
	{

		// try to figure out the
		// path of the template
		$base = Config::get('paths/page_templates', true);

		// include the page
		$pg = self::initModule(array('class'=>p('template')), array('parent'=>array(), 'template'=>array()), 'pages');

		// now make sure the page exists
		if ( !$pg ) {
			show_404();
		}

		$r = $pg->render( array() );

		// parse tmpl
		list($html, $js) = self::parseHtmlForScriptTags($r->html);

		// output
		header("Content-Type:text/javascript");

		// print
		exit( json_encode( array("stat" => 1, 'html' => $html, 'bootstrap' => array( 'c' => @$page->args['bodyClass'], 'js' => $js ) ) ) );

	}

	static function ajax()
	{

		// module
		$module = p('_module');
		$origin = p('_origin');
		$type = p('_type', 'modules');


		// set it
		$mod = self::initModule(array('class'=>$module), array(), $type);

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
			exit( json_encode( array_merge(array('stat'=>1), $resp) ) );

		}

	}


	static function rss()
	{

		// module
		$module = p('_module');
		$type = p('_type', 'modules');

		// set it
		$mod = self::initModule(array('class'=>$module), array(), $type);

		// check the module
		if ( !$mod ) {
			show_404();
		}

		// call xhr
		$resp = $mod->rss();

		// header
		header("Content-Type:application/rss+xml");

		// prit
		exit( $resp );



	}


	static function html($page, $project=false)
	{

		// try to figure out the
		// path of the template
		$base = Config::get('paths/pages', true);

		// if there's a project
		// we need to add it's folder
		if ( $project ) {
			$base .= "{$project}/";
		}

		// include the page
		$pg = self::initModule(array('class'=>$page), array('parent'=>array(), 'template'=>array()), 'pages');

		// now make sure the page exists
		if ( !$pg ) {
			b::show_404();
		}

		// render
		// html
		$o = $pg->render( self::$globals );

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
		$args['cssEmbeds'] = Controller::getEmbedList('css', true);
		$args['jsEmbeds'] = Controller::getEmbedList('js', true);

		// since this is special, we need to do it
		// ourself
		$global = self::replaceTokens($global->html, $args, true);

		// try getting our hostname
		$hn = apc_fetch("server_hostname");

		// get it
		if ( !$hn ) {
			$hn = trim(`hostname`);
			apc_store('server_hostname', $hn);
		}

		// add to global
		$global .= "\n<!-- {$hn} - ".date('r')." -->";

		// now replace the body
		exit( $global );

	}

	public static function renderPage($file, $p_args=array())
	{
		return self::renderTemplate(
			$file,
			$p_args,
			Config::get('paths/pages')
		);
	}

	public static function renderModule($file, $p_args=array(), $base=false)
	{
		return self::renderTemplate($file, $p_args, $base);
	}

	public static function renderTemplate($file, $p_args=array(), $base=false)
	{

		// try to figure out the
		// path of the template
		if ( !$base ) {
			$base = Config::get('paths/modules', true);
		}

		$base = "/" . trim($base, '/') . "/";

		// check for page
		if ( strpos($file, '.php') === false ) {
			$file .= ".template.php";
		}

		// get any globals
		$p_args = array_merge( self::$globals, $p_args );

		// start ob buffer
		ob_start();

		// define all
		foreach ( $p_args as $k => $v ) {
			$$k = $v;
		}

		// include the page
		include $base.$file;

		// stop
		$page = ob_get_contents();

		// clean
		ob_clean();

		// give it back
		return self::renderString($page, $p_args, $base);

	}

	public static function renderString($str, $p_args=array(), $base=false)
	{


		// parse the template
		list($template, $modules, $tmpl_settings) = self::parseTemplate($str);

		// now we loop through each
		// one of the modules and create it
		foreach ( $modules as $i => $m ) {

			// set it
			$mod = self::initModule($m, array('parent'=>$p_args));

			// no mod continue
			if ( !$mod ) { continue; }

			// html
			$o = $mod->render($m['cfg']);

			if ( $o ) {

				// render
				$template = str_replace("<module{$m['id']}>", $o->html, $template);

				// add to list
				$modules[$i]['html'] = $o->html;
			}

			$modules[$i]['ref'] = $mod;

		}

		// template
		$template = self::replaceTokens($template, $p_args);

		// object
		$o = new StdClass();

		// set some stuff
		$o->html = $template;
		$o->modules = $modules;
		$o->args = $p_args;


		// give it back
		return $o;

	}

	public function replaceTokens($template, $d_args, $cleanup=false)
	{

		// args
		$args = array();

		// take out any object
		foreach ( array_merge( self::$globals, $d_args ) as $k => $v ) {
			if ( !is_object($v) ) {
				$args[$k] = $v;
			}
		}

		// parse the template
		if ( preg_match_all('#\{\$([a-z0-9\.\-\|\_\s\']+)\}#i', $template, $matches, PREG_SET_ORDER) ) {

			// loop through each
			foreach ( $matches as $match ) {

				// check for a defualt
				$key = array_shift(explode('|', $match[1]));
				$default = "";
				$value = false;

				// no default
				if ( strpos($match[1], '|') !== false ) { $default = array_pop(explode('|', $match[1])); }

				// first see if it's in args alone
				if ( array_key_exists($key, $args) ) {
					$value = $args[$key];
				}

				// url
				else if ( strpos($key, 'url.') !== false ) {
						$value = Config::url(str_replace('url.', '', $key));
					}

				// see if there are . in the name
				else if ( strpos($key, '.') !== false ) {

						// break apart
						$parts = explode('.', $key);

						// value
						$k = $args;

						// loop through
						foreach ( $parts as $p ) {
							if ( $k and array_key_exists($p, $k) ) {
								$k = $k[$p];
							}
							else { $k = $default; break; }
						}

						// replace it
						$value = $k;

					}

				// if value go ahead and replace
				if ( $value === false and $default and !$cleanup ) {
					$value = $default;
				}

				// replace
				if ( $value !== false ) {
					$template = str_replace($match[0], $value, $template);
				}

			}

		}

		// re
		return $template;

	}

	public function initModule($mod, $args, $type='modules')
	{

		// include
		list($_m, $bolt) = self::includeModule($mod['class'], $type);

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
		if ( property_exists($m, 'embeds') ) {

			// get htem
			$embeds = $m::$embeds;

			// if js
			if ( isset($embeds['js']) ) {
				foreach ( $embeds['js'] as $name => $file ) {
					self::addEmbed('js', $name, $file);
				}
			}

		}

		// save
		self::$modules[$mod['class']] = $m;

		// give back
		return $m;

	}

	public function parseTemplate($string)
	{

		// modules
		$modules = array();
		$settings = array();

		// lets start parsing
		if ( preg_match_all("/\{%\s?([a-z\_]+)(\([^\)]+\))?\s?%\}/", $string, $match, PREG_SET_ORDER) ) {

			// loop through each module and
			// figure out what is there
			foreach ( $match as $m ) {
				if ( substr($m[0], 1) != '$' ) {

					$i = self::$id++;

					// add the module
					$mod = array( 'id' => $i, 'class' => trim($m[1]), 'cfg' => array() );

					// isset
					if ( isset($m[2]) ) {

						// args
						$args = trim($m[2], '()');

						// has at least 1 :
						if ( strpos($args, ':') !== false ) {
							foreach ( explode(',', $args) as $a ) {
								list($k, $v) = explode(':', $a);
								$mod['cfg'][$k] = $v;
							}
						}
						else {
							$mod['cfg'] = unserialize(base64_decode($args));
						}

					}

					// modules
					$modules[] = $mod;

					// replace
					$string = preg_replace("/".preg_quote($m[0], '/')."/", "<module{$i}>", $string, 1);

				}
			}

		}

		// lets start parsing
		if ( preg_match_all("/\{\#([a-zA-Z\_]+)\:\s?([^\#]+)\#\}/", $string, $match, PREG_SET_ORDER) ) {
			foreach ( $match as $m ) {
				$settings[trim($m[1])] = trim($m[2]);
				$string = str_replace($m[0], "", $string);
			}
		}

		// just give back
		return array($string, $modules, $settings);

	}


	public static function parseHtmlForScriptTags($body)
	{

		// need to remove comments
		$body = preg_replace(array("/\/\/[a-zA-Z0-9\s\&\?\.]+\n/", "/\/\*(.*)\*\//"), " ", $body);

		// javascript
		$jsInPage = preg_match_all("/((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))/i", $body, $js);


		// if yes remove
		if ( $jsInPage ) {
			$body = preg_replace("/((<[\s\/]*script\b[^>]*>)([^>]*)(<\/script>))/i", "", $body);
		}

		// give back
		return array($body, @$js[3]);

	}

	public static function addEmbed($type, $name, $project, $file) {
		self::$embeds[$type][$name] = array('file' => Config::asset($type, $file, $project) );
	}
	
	public static function getEmbedList($type) {
		
		// config
		$config = array();
		
			// check config;
			if ( Config::get('embeds/'.$type) ) {
				$config = Config::get('embeds/'.$type);
			}
		
		// list
		$list = array();
		
		// print the list
		foreach (  array_merge(self::$embeds[$type], $config) as $name => $file ) {
			if ( $type == 'css' ) {
				$list[] = "<link rel='stylesheet' href='/assets/{$file}' type='text/css'>";
			}
			else if ( $type == 'js') {
				$n = key($file);
				$list[$n] = array('file' => "/assets/".$file[$n]);
			}
		}
	
		return ($type=='css' ? implode("\n",$list) : json_encode($list) );
	
	}

	public static function printCfg($a, $rtn=false) {
		$c = base64_encode(serialize($a));
		if ( $rtn ) {
			return $c;
		}
		else {
			echo $c;
		}
	}

}


?>