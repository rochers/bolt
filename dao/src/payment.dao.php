<?php

// namespace
namespace dao;

// payment dao
class payment extends \DaoDb {

	// track changes
	protected $trackChanges = true;

	// errors
	public $errors = false;

	// public
	public static $statuses = array(
		'A' => "Authorized",
		'S' => "Settled",
		'V' => "Voided",
		'R' => "Refunded"
	);

	protected $data = array(
		'user' => false,
		'status' => false,
		'meta' => false,
		'publisher' => false,
		'trans_id' => false,
		'added_ts' => false,
		'modified_ts' => false,
		'changelog' => false,		
	);
	
	protected $schema = array(
		'user' => array( 'type' => 'dao', 'class' => 'user' ),
		'meta' => array( 'type' => 'json' ),
		'publisher' => array( 'type' => 'dao', 'class' => 'publisher' ),
		'changelog' => array( 'type' => 'json' ),
	);

	// get 
	public function get($id) {
		
		// get it 
		$row = $this->row("SELECT * FROM payments as p WHERE p.id = ? ",array($id));
		
			// no
			if ( !$row ) {
				return $row;
			}
		
		// set 
		$this->set($row);
		
	}
	
	public function set($row) {
	
		// set
		parent::set($row);	
	
		$this->private['f_total'] = money_format("%=.2n",(float)$this->data['meta']['price']);
		
		if ( !array_key_exists($this->status,self::$statuses) ) {
			$this->status = 'A';
		}
		
		$this->private['f_status'] = self::$statuses[$this->status];
	
	}
	
	public function save() {
	
		// normailze our data
		$data = $this->normalize();	
	
		if ( $this->id ) {
		
			// try to save
			$sql = " 
				UPDATE payments
				SET 
					`status` = ?,
					`user` = ?,
					`meta` = ?,
					`trans_id` = ?,
					`publisher` = ?,					
					`modified_ts` = ?,
					`changelog` = ?
				WHERE `id` = ?
			";
		
			// run it
			$r = $this->query($sql,array(
					$data['status'],
					$data['user'],
					$data['meta'],
					$data['trans_id'],
					$data['publisher'],					
					utctime(),
					$data['changelog'],
					$this->id,					
				));
		
			// what happened
			if ( $r === false ) {
				$this->id = false;
			}
		
		}
		else {
			
			// set the id
			$this->data['id'] = trim(`/usr/bin/uuid`);
		
			// try to save
			$sql = " 
				INSERT INTO payments
				SET 
					`id` = ?,
					`status` = ?,
					`user` = ?,
					`meta` = ?,
					`trans_id` = ?,
					`publisher` = ?,
					`added_ts` = ?,
					`modified_ts` = ?
			";
		
			// run it
			$r = $this->query($sql,array(
					$this->data['id'],
					$data['status'],
					$data['user'],
					$data['meta'],
					$data['trans_id'],
					$data['publisher'],
					utctime(),
					utctime()					
				));
		
			// what happened
			if ( $r === false ) {
				unset($this->data['id']);
			}
		
		}
	
	}
	
	public function delete() {
		$this->query(" DELETE FROM payments WHERE id = ? LIMIT 1 ",array($this->id));	
	}	
	
	private function _initBt() {		
		\Braintree_Configuration::environment( \Config::get('site/bt-env') );
		\Braintree_Configuration::merchantId( \Config::get('site/bt-merch') );
		\Braintree_Configuration::publicKey( \Config::get('site/bt-pub') );
		\Braintree_Configuration::privateKey( \Config::get('site/bt-priv') );	
	}
	
	////////////////////////////////////////////////////////////////
	/// @brief attempt to process the credit card transaction for sale
	///
	/// @params $cfg sale configuration
	/// @return bool respose of payment
	///
	///
	///	$cfg = array(
	///		'billing' => array(
	///			'firstname' => "travis",
	///			'lastname' => "kuhl",
	///			'street' => "2000 main st apt 128",
	///			'city' => "santa monica",
	///			'state' => "ca",
	///			'zip' => "90405",
	///		),
	///		'card' => array(
	///			'number' => '411111111111111',
	///			'exp' => '02/2012',
	///			'code' => '111'
	///		),
	///		'amount' => array(
	///			'total' => '25.00',
	///			'items' => array()
	///		),
	///		'items' => array(
	///			array(
	///				'id' => "1",
	///				'type' => 'D',
	///				'title' => "This is the title",
	///				'desc' => "this is the descr",
	///				'quantity' => "1",
	///				'per' => "25.00",
	///				'url' => "http://",
	///			)
	///		)
	/// );
	///
	////////////////////////////////////////////////////////////////
	public function sale($cfg) {
		
		// lets break cfg into peices for short access
		$a = $cfg['amount'];
		$c = $cfg['card'];
		$b = $cfg['billing'];
		$i = $cfg['items'];
					
		// first we need to create our payment record
		$this->meta_price = $a['total'];
	
		// so lets save now and see what we should do
		$this->save();
		
			// if no id than something horrible happened and
			// we should throw an erro
			if ( $this->id === false ) {
				throw new \Exception("Could not create a payment record"); return;
			}
	
		// rec
		$rec = array(
		    'amount' => $a['total'],
		    'creditCard' => array(
				'number' => $c['number'],
				'expirationDate' => $c['exp'],
				'cvv' => $c['code']
		    ),
		    'billing' => array(
		    	'firstName' => $b['firstname'],
		    	'lastName' => $b['lastname'],
		    	'streetAddress' => $b['street'],
		    	'locality' => $b['city'],
		    	'region' => $b['state'],
		    	'postalCode' => $b['zip'],
		    	'countryName' => 'United States of America'
		    ),
		    'customFields' => array(
		    	'pid' => $this->id
		    ),
		    'options' => array(
		    	'submitForSettlement' => true,
		    	'storeInVault' => true,
		    	'addBillingAddressToPaymentMethod' => true,
		    )
		);	
		
		// init
		$this->_initBt();				
		
		// setTag
		$setTag = false;
		
	/*
		// check for custoemr
			$cc = \Braintree_Customer::create(array(
		    	'id' => $this->user->id,
		    	'firstName' => $this->user->firstname,
		    	'lastName' => $this->user->lastname,
		    	'email' => $this->user->email,
			    'creditCard' => array(
					'number' => $c['number'],
					'expirationDate' => $c['exp'],
					'cvv' => $c['code']
			    ),		    					
			));
		
			// check the user for a payment:profile tag
			// if not they havne't been created as a user
			// in the payment system, so create them
			$rec['customerId'] = $this->user->id;
			
*/
		// lets set what happens
		try {
		
			$result = \Braintree_Transaction::sale($rec);
			
			// check the result
			if ( $result->success AND ( $result->transaction->status == 'authorized' OR $result->transaction->status == 'submitted_for_settlement' ) ) {				

				// user has a profile
				if ( $setTag ) {
					$this->user->tags->add('payment','profile');
					$this->user->save();
				}

				// update with our trans id
				$this->trans_id = $result->transaction->id;
				
				// now lets figure out what to save
				$this->meta_ammout = $a;
				$this->meta_items = $i;
				$this->meta_card = substr($c['number'],-4);
				
				// status is a for authorized
				$this->status = 'A';
				
				// save back
				$this->save();
				
				// all good
				return true;
			
			}
			else {
				
				// delete our payment
				$this->delete();
							
				// throw an error
				throw new \Exception( "Error Processing Your Credit Card" );
				
			}
	
			
		}
		catch ( \Braintree_Result_Error $e ) {
		
			// delete our transactio
			$this->delete();
		
			// throw an error
			throw new \Exception("Error Processing Your Credit Card (".$e->getMessage().")"); return;
			
		}
		catch ( \Exception $e ) {
	
			// delete our transactio
			$this->delete();		
		
			// throw an error
			throw new \Exception("Error Processing Your Credit Card (".$e->getMessage().")"); return;
		}


	}
	

	
	public function find() {

		// init
		$this->_initBt();
			
		// find it 		
		try {	
			$t = \Braintree_Transaction::find($this->trans_id);
		}
		catch ( \Braintree_Exception_NotFound $e ) {
			return array(
				'status' => false,
				'error' => $e->getMessage()
			);
		}
		catch ( \Exception $e) {
			return array(
				'status' => false,
				'error' => $e->getMessage()
			);
		}

		$o = array(
			'id' => $t->id,
			'status' => $t->status,
			'billing' => array(
				'id' => $t->billingDetails->id,
				'firstName' => $t->billingDetails->firstName,
				'lastName' => $t->billingDetails->lastName,				
				'streetAddress' => $t->billingDetails->streetAddress,
				'city' => $t->billingDetails->locality,				
				'state' => $t->billingDetails->region,
				'zip' => $t->billingDetails->postalCode,												
			),
			'card' => array(
				'token' => $t->creditCardDetails->token,
				'bin' => $t->creditCardDetails->bin,
				'last4' => $t->creditCardDetails->last4,
				'cardType' => $t->creditCardDetails->cardType,
				'exp' => $t->creditCardDetails->expirationDate,
				'token' => $t->creditCardDetails->token,
				'masked' => $t->creditCardDetails->masked,
			),
			'refund' => ( is_string($t->refundId) ? $t->refundId : '--' ),
			'history' => array()
		);
	
		// add history
		foreach ( $t->statusHistory as $i ) {
			$ts = $i->timestamp->getTimestamp() + rand(1,3);
			$o['history'][$ts] = array(
				'ts' => $ts,
				'status' => $i->status,
				'amount' => $i->amount,
				'user' => $i->user ? $i->user : '?',
				'source' => $i->transactionSource ? $i->transactionSource : '?'
			);
		}
		
		// sort
		krsort($o['history']);

		// give it back
		return $o;
	
	}
	
	public function refund($reason=false) {
	
		// init
		$this->_initBt();	
	
		// first find out if we have already settled
		$t = $this->find();
	
		// resp
		$r = false;
		$s = false;
		
		// log the attempt
		$cl = $this->changelog;
		
			// changelog
			$cl[time()] = array(
				'text' => 'Void/Refund attempted! Reason: ' . $reason,
				'name' => \Session::getUser()->name,
				'id' => \Session::getUser()->id,				
			);				
		
		// try
		try {
		
			// what do to now
			switch( $t['status'] ) {
			
				// if we've authorized we need to void
				case 'submitted_for_settlement':				
				case 'authorized';
					$s = 'V';
					$r = \Braintree_Transaction::void($this->trans_id); break;
			
				// if we've settled we need to refund
				case 'settled':
					$s = 'R';
					$r = \Braintree_Transaction::refund($this->trans_id); break;
					
				// default
				default:
					return array(
						'status' => false,
						'error' => "Unable to determin transaction status. Need to void through Braintree"
					);
						
			};
			
		}
		catch ( \Braintree_Exception_NotFound $e ) {
		
			// save
			$this->save();		
		
			return array(
				'status' => false,
				'error' => $e->getMessage()
			);
			
		}
		catch ( \Exception $e) {
		
			// save
			$this->save();		
			
			return array(
				'status' => false,
				'error' => $e->getMessage()
			);
			
		}		

		
		// if good we need to save this 
		if ( $r AND $r->success ) {

			// set the status
			$this->status = $s;

			// changelog
			$cl[time()+1] = array(
				'text' => 'Void/Refund completed!',
				'name' => \Session::getUser()->name,
				'id' => \Session::getUser()->id,				
			);
						
			
		}
		else if ( $r ) {
	
			// return array
			return array(
				'status' => false,
				'obj' => $r,
				'error' => "Braintree returned an error"
			);		
		
		}
		
		// reset changelog
		$this->changelog = $cl;
		
		// save
		$this->save();
	
		// lets see what happened
		return array(
			'status' => ($r?$r->success:false),
			'obj' => $r,
			'error' => "Unknow error occurred. Try again or go to Braintree to void payment"
		);
	
	}

}

?>