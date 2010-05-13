<?php

// bolt secure
function bsecure_get($db,$key,$section=false) {

	// section
	if ( defined('BOLT_SECURE_SECTION') ) {
		$section = BOLT_SECURE_SECTION;
	}
	
	// cache id
	$cid = "bolt:db:{$db}";

	// check for this db 
	$cache = apc_fetch($cid);

	// if not there we load for the first time
	if ( !$cache ) {
	
		// ifle
		$file = "/home/bolt/conf/bsecure/{$db}.ini";
		
		// try to open the fir
		if ( file_exists($file) ) {
		
			// get it 
			$cache = parse_ini_file($file,true);
				
			// cache it 
			apc_store($cid,$cache);
			
		}

	}
	
	// no cache we stop
	if ( !$cache ) {
		return false;
	}
	
	// does the section exsit
	if ( $section ) {
		if ( array_key_exists($section,$cache) AND array_key_exists($key,$cache[$section]) ) {
			return $cache[$section][$key];
		}
	}
	else {
		if ( array_key_exists($key,$cache) ) {
			return $cache[$key];
		}
	}

	// nope
	return false;

} 

?>