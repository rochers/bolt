YUI.add("bolt-countdown",function(Y) {

	// shortcuts
	var $ = Y.get, $j = Y.JSON;

	// base 
	BLT.Class.Countdown = function(args) {
		this.init(args);
	}

	// base prototype
	BLT.Class.Countdown.prototype = {
		
		store: {},
		
		init : function(args) {
					
			this.id = args.id;
			
			if (Y.one('#days')) { 
				this.days = parseInt(Y.one('#days').get('innerHTML'));
			} else { 
				this.days = 0;
			}
			
			this.hours = parseInt(Y.one('#hours').get('innerHTML'));
			this.mins = parseInt(Y.one('#mins').get('innerHTML'));
			this.secs = parseInt(Y.one('#secs').get('innerHTML'));
			
			this.endtime = args.endtime;
			
			// do some date calculation
			var dthen = this.endtime*1000;
			var dnow = new Date();
			var ddiff = new Date(dthen-dnow);
			this.gsecs = Math.floor(ddiff.valueOf()/1000);
			
			this.countBack();
			
			// publish ini
			this.publish('countdown:init');
			
			// fire it 
			this.fire('countdown',this);
													
		}, 
		
		
		countBack : function() { 
		
			if (this.gsecs < 0) {
			    return;
			}
			
			  this.days = this.calculateAge(86400,100000);
			  this.hours = this.calculateAge(3600,24);
			  this.mins = this.calculateAge(60,60);
			  this.secs = this.calculateAge(1,60);
			
			  this.updateLabels();
			  
			  this.gsecs = this.gsecs - 1;
			  
			  var q = new Y.AsyncQueue();	
			  	  q.add({
					       fn: this.countBack,
					       timeout: 1000,
					       context: this
					    });
			  	  q.run();		  
					  		
		},
		
		
		calculateAge : function(num1,num2) { 
			
			var s = ((Math.floor(this.gsecs/num1))%num2).toString();
			
			//if (s.length < 2)
    		//	s = "0" + s;
			
			return s; 
		
		},
		
		
		updateLabels : function() { 
		
			if (Y.one('#days')) { 
				Y.one('#days').set('innerHTML',this.days);
			} 
			
			Y.one('#hours').set('innerHTML',this.hours);
			Y.one('#mins').set('innerHTML',this.mins);
			Y.one('#secs').set('innerHTML',this.secs);
		
		
		}
				
		
	}
	

	// we fire some custom events
	Y.augment(BLT.Class.Countdown, Y.EventTarget);	
	
});	