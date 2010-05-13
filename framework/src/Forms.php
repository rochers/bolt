<?php

class Forms {
	
	public static $arg = "f";

	public static function field($cfg=array()) {
		
		// set some stuff
		$fid	= $cfg['name'];
		$type	= p('type','input',$cfg);
		$val	= p('value',false,$cfg);
		$other  = p('other',array(),$cfg);
		
		// attr
		$attr = array();
		$parts = array();
		
		// get the form
		$fval = self::validate( $cfg, p( self::$arg ) );
	
			// try
			if ( !$val AND $fval ) {
				$val = $fval;
			}

	
		// switch based on type
		switch($type) {

			// type
			case 'hidden':
				$attr = array('type'=>'hidden','size'=>false); 
				$parts[] = array("<span>{$val}</span><input",'attr',">");				
				break;

			// type
			case 'password':
				$attr = array('type'=>'password','size'=>false); 
				$parts[] = array("<input",'attr',">");				
				break;
			
			// type
			case 'input':
				$attr = array('type'=>'text','size'=>false); 
				$parts[] = array("<input",'attr',">");
				break;
			
			// password
			case 'password':
				$attr = array('type'=>'password','size'=>false); 
				$parts[] = array("<input",'attr',">");				
				break;			
				
			// textarea	
			case 'textarea':
			
				$attr = array('value'=>'-',"rows"=>false,"cols"=>false);			
				$parts[] = array("<textarea",'attr',">$val</textarea>");
				break;
				
		
			// select
			case 'select':

						
				$attr = array("value"=>'-');
				$parts[] = array("<select",'attr',"><option></option>");			
					foreach ( $cfg['opts'] as $k => $v ) {
						$parts[] = array("<option ".((string)$k==$val?'selected=selected':'')." value='$k'>$v</option>");
					}					
				$parts[] = array("</select>");
				break;
				
			// datetime				
			case 'datetime':
				
				// try to get the default
				$m = $d = $y = $hh = $mm = $ss = false;
				
					// check it 
					if ( $val ) {
						$m = (int)date('m',$val);
						$d = (int)date('d',$val);
						$y = (int)date('Y',$val);
						$hh = (int)date('G',$val);
						$mm = (int)date('i',$val);
					}
				
				$mons = array();
				$days = array();
				$years = array();
				$hours = array();
				$ms = array();
				
				$ey = p('ey',(date('Y')-100),$cfg);
				$sy = p('sy',date('Y'),$cfg);
				
				// opts
				for ( $i = 1; $i <= 12; $i++ ) {
					$mons[$i] = date(p('monF',"F",$cfg),strtotime("2009-{$i}-01"));
				}
				for ( $i = 1; $i <= 31; $i++ ) {
					$days[$i] = $i;
				}
				for ( $i = $sy; $i >= $ey; $i-- ) {
					$years[$i] = $i;
				}
				for ( $i = 0; $i < 24; $i++ ) {
					$h = $i % 12;
					$hours[$i] = ($h==0 ? 12 : $h);
				}
				for ( $i = 0; $i < 60; $i++ ) {
					$ms[$i] = sprintf("%20d",$i);
				}
				
				// year by default
				$yt = ( isset($cfg['hideY']) ? 'hidden' : 'select' );
				
				// force year
				if ( isset($cfg['y']) ) { $y = $cfg['y']; }
				
				// 
				$parts[] = array( 
					self::field(array( 'name' => "{$fid}][m", 'type' => 'select', 'opts' => $mons, 'class' => 'a', 'value' => $m )),
					" / ", 
					self::field(array( 'name' => "{$fid}][d", 'type' => 'select', 'opts' => $days, 'class' => 'a', 'value' => $d )),
					" / ",
					self::field(array( 'name' => "{$fid}][y", 'type' => $yt, 'opts' => $years, 'class' => 'a', 'value' => $y )),
					p('seperate',"&nbsp; &nbsp;",$cfg),
					self::field(array( 'name' => "{$fid}][hh", 'type' => 'select', 'opts' => $hours, 'class' => 'a', 'value' => $hh )),
					" : ",
					self::field(array( 'name' => "{$fid}][mm", 'type' => 'select', 'opts' => $ms, 'class' => 'a', 'value' => $mm )),
				);						
					
			break;
			
			// datetime				
			case 'date':
				
				// try to get the default
				$m = $d = $y = false;
				
					// check it 
					if ( $val ) {
						$m = (int)date('m',$val);
						$d = (int)date('d',$val);
						$y = (int)date('Y',$val);
					}
				
				$mons = array();
				$days = array();
				$years = array();
				
				$ey = p('ey',(date('Y')-100),$cfg);
				$sy = p('sy',date('Y'),$cfg);
				
				// opts
				for ( $i = 1; $i <= 12; $i++ ) {
					$mons[$i] = date(p('monF',"F",$cfg),strtotime("2009-{$i}-01"));
				}
				for ( $i = 1; $i <= 31; $i++ ) {
					$days[$i] = $i;
				}
				for ( $i = $sy; $i >= $ey; $i-- ) {
					$years[$i] = $i;
				}
				
				// year by default
				$yt = ( isset($cfg['hideY']) ? 'hidden' : 'select' );
				
				// force year
				if ( isset($cfg['y']) ) { $y = $cfg['y']; }
				
				// 
				$parts[] = array( 
					self::field(array( 'name' => "{$fid}][m", 'type' => 'select', 'opts' => $mons, 'class' => 'a', 'value' => $m )),
					" / ", 
					self::field(array( 'name' => "{$fid}][d", 'type' => 'select', 'opts' => $days, 'class' => 'a', 'value' => $d )),
					" / ",
					self::field(array( 'name' => "{$fid}][y", 'type' => $yt, 'opts' => $years, 'class' => 'a', 'value' => $y )),
				);						
					
			break;			
			
			// radio
			case 'radio':
				$attr = array('type'=>'checkbox');
				foreach ( $cfg['opts'] as $k => $v ) {
					$parts[] = array("<label><input",'attr',($k==$val?'checked=checked':'')."> {$v}</label> ");
				}
				break;
				
			// case 				
			case 'checkbox':

				if( isset($other['opts']) ) {
				
				}
				else {
					$attr = array('type'=>'checkbox');
					$parts[] = array("<label><input",'attr',($val=='true' || $val =='checked'?'checked=checked':'')."></label> ");
				}
				break;
		
			// monitary
			case 'monetary':				
			
				// value
				$d = $c = "00";
				
					if ( $val ) {
						$p = explode('.',$val);
						if ( count($p == 0) ) {
							$d = $p[0];
						}
						else {
							$c = array_pop($p);
							$d = implode('.',$p);
						}
					}
				
				$attr = array('type'=>'text'); 
				$parts[] = array(
					"<span class='monetary'>\$ ",
					self::field(array( 'name' => "{$fid}][d", 'type' => 'input', 'size' => '5', 'class'=>'r', 'value' => $d )),
					".",
					self::field(array( 'name' => "{$fid}][c", 'type' => 'input', 'size' => '2', 'value' => $c )),
					"</span>"
				);
				break;				
		
		}
		
		if ( isset($cfg['class']) ) {
			$attr['class'] = $cfg['class'];
		}
		
		$html = array();
		
		$attr = array_merge(array('name' => self::$arg."[{$fid}]",'value'=>$val),$attr,$other);
		
		foreach ( $parts as $part ) {
			foreach ( $part as $p ) {
				
				// is it attr
				if ( $p == 'attr' ) {
					
					// loop through
					foreach ( $attr as $key => $val ) {
						if  ( $val AND $val != '-' )  { 
							$html[] = "{$key}='$val'";
						}
						else if ( !$val AND array_key_exists($key,$cfg)) {
							$html[] = "{$key}='".$cfg[$key]."'";
						}
					}
					
				}
				else {
					$html[] = $p;
				}
	
			}
		}
		
		// give back
		return implode(" ",$html);
			
	}

	public static function validate($field,$values) {
		
		// name
		$name = $field['name'];

		// value
		$val = p($name,false,$values);

		// try to validate
		switch( p('type','input',$field) ) {
			
			case 'datetime':
				
				// values
				$a = $values[$name];
				
				// construct
				$val = strtotime("{$a['y']}-{$a['m']}-{$a['d']} {$a['hh']}:{$a['mm']}:00");
			
			break;

			case 'date': 

				// values
				$a = $values[$name];

				// construct
				$val = strtotime("{$a['y']}-{$a['m']}-{$a['d']} 01:00:00");
			
				
			break;			
			
			case 'monetary':			
				$val = $values[$name]['d'].".".$values[$name]['c'];
			break;
				
		
		};		
		
		// check the value
		if ( ( !isset($field['required']) OR ( isset($field['required']) AND $field['required'] != false ) ) AND $val == "" ) {
			return false;
		}

		// return
		return $val;
	
	}
	
	public static function validation($fields) {
		
		// get the fuelds
		$f = p( self::$arg );
		
		// sg
		$error = array();
		$values = array();
		
		// loop through feidsl
		foreach ( $fields as $name => $field ) {
			
			// name
			$info = $field[1];
			$ok = false;
		
			if ( array_key_exists($info['name'],$f) )  {
				
				// value
				$values[$name] = self::validate( $info, $f );
			
				// value
				if ( ( isset($info['required']) AND ( $info['required'] === false ) ) OR ($values[$name] AND $values[$name] != "") ) {
					$ok = true;
				}
				else {
					$error[] = "$field[0] is blank!";
				}
				
			}
			else {
				$values[$name] = false;
			}
		
		}
		
		return array( 'values' => $values, 'errors' => $error );
	
	}

}

?>