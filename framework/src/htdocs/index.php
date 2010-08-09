<?php
        
	// framework
	define("FRAMEWORK",	"/home/bolt/share/pear/bolt/framework/");
	define("CONFIG",	"/home/bolt/config/");
	define("_404",		"/home/bolt/share/htdocs/404.php");
	
	// dev
	define("DevMode",( getenv("bolt_framework__dev_mode") == 'true' ? true : false ));
	
	// project
	define("PROJECT", getenv("boltProject"));
	
	// include our Bold file
	require(FRAMEWORK . "Bolt.php");
	
	//figure out the project name passed from apache rewrite
	$project = PROJECT;
		
		// no project we show a 404
		if ( $project === false ) {
			exit( include(_404) );
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