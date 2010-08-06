<?php
        

	// framework
	define("FRAMEWORK",	"/home/bolt/share/pear/bolt/framework/");
	define("CONFIG",	"/home/bolt/config/");
	define("_404",		"/home/bolt/share/htdocs/404.php");
	
	// dev
	define("DevMode",( getenv("bolt_framework__dev_mode") == 'true' ? true : false ));
	
	// include our Bold file
	require(FRAMEWORK . "Bolt.php");
	
	//figure out the project name passed from apache rewrite
	$project = getenv("boltProject");
		
		// no project we show a 404
		if ( $project === false ) {
			exit( include(_404) );
		}
	
	// we need their project config
	Config::load(CONFIG . $project . ".ini");
	
		// add dao to autoload
		if ( is_array(Config::get('autoload/file')) ) {
			foreach ( Config::get('autoload/file') as $file ) {
				$GLOBALS['_auto_loader'][] = $file;
			}
		}

	// $class
	$class = Config::get('site/base');

		// no claas
		if ( $class === false OR !class_exists($class, true) ) {
			exit( include(_404) );
		}	

	//kick off the project and get back page params
	$class::start();
	
	// pre-route
	$class::prePage();
	
	//get page
	$page = $class::getPage(); 
	
	// pre-route
	$class::preRoute($page);
	
	//kick off our page assembly
	Controller::route($page);
        
?>