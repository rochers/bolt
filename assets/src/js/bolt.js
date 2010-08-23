
// define our bolt instance 
// we'll init YUI below
// new yui
var f = function(Y) { 

	// called to load
	Y.on('domready',function(){ BLT.Obj = new BLT.Base(); BLT.execute('l'); },window);

	// shortcuts
	var $ = Y.get, $j = Y.JSON;

	// check for browser 			
	if ( /opera/.test(navigator.userAgent.toLowerCase()) ) { $(document.body).addClass('opera'); }
	if ( /firefox/.test(navigator.userAgent.toLowerCase()) ) { $(document.body).addClass('firefox'); }	
	if ( /chrome/.test(navigator.userAgent.toLowerCase()) ){ $(document.body).addClass('chrome'); }
	if ( /safari/.test(navigator.userAgent.toLowerCase()) ) { $(document.body).addClass('safari'); } 		
	if ( /msie/.test(navigator.userAgent.toLowerCase()) ) { $(document.body).addClass('ie'); }

	// load images
	var img = new Y.ImgLoadGroup({ timeLimit: 2, foldDistance: 30 }); img.set('className', 'defer');

	// base 
	BLT.Base = function() {
		this.init();
	}

	// base prototype
	BLT.Base.prototype = {
		
		// args
		store : { },
		class : {},
		
		// init 
		init : function() {
		
			// attach some stuff
			$('#doc').on('click',this.click,this);
			$('#doc').on('mouseover',this.mouse,this);			
			$('#doc').on('mouseout',this.mouse,this);
			$('#doc').on('keyup',this.keyup,this);
			
			// beed to check form tags to see if 
			// they should open in a panel
			$('#doc').all('form.open-panel').each(function(el){
				
				// get attr
				var action = el.getAttribute('action');
				
				// reset it 
				el.setAttribute('x-action', self.getUrl(action,{'.context':'xhr'}));
				el.setAttribute('action','#');
				el.setAttribute('method','get');													
											
				// attach to submit
				el.on('submit',function(e){	
				
					// halt what the browser wants to do
					e.halt(); 
							
					// get target
					var tar = e.target;
					
					// has class
					if ( tar.hasClass('loading') ) {
						return;
					}
					
					// loading
					tar.addClass('loading');
					
					// get the action
					var url = tar.getAttribute('x-action');
					
					// get the form
					BLT.Obj.panel.load(url,{'form':tar,'openAfter':true});
		
					// remove
					tar.removeClass('loading');
					
					
				},this);				
			
			});
						
			// self
			var self = this;
			
			// generl panel
		//	this.panel = new BLT.Class.Panel({});			
			
			// events
			this.publish('blt-base:docclick');			
			this.publish('blt-base:mouse');
			this.publish('blt-base:tblcheckboxclick');
			this.publish('blt-base:resize');			
			
			// fb events			
			this.publish('blt-base:fb-init');
			this.publish('blt-base:fb-not-con');
			this.publish('blt-base:fb-con');						

		},
		
		fbInit : function () {
	
			// load facebook
			Y.Get.script(document.location.protocol + '//connect.facebook.net/en_US/all.js',{
				'insertBefore': 'ft',
				'scope': BLT.Obj,
				'onSuccess': function(){ 
	
					// yes to the fb
					BLT.Env.fb = true;			
					
					// self
					var self = this;
					
					// init
					FB.init({appId: BLT.Env.fbApiKey, status: false, cookie: true, xfbml: true});
													
				}
				
			});	
		
		
		},		
		
		// load css
		loadCss : function(url) {
			Y.Get.css(url);
		},
		
		loadJs : function(url,args) {		
			Y.Get.script(url,args);
		},
	
		// click
		click : function(e) {
		
			this.fire('blt-base:docclick',e);
		
			// target
			var tar = oTar = e.target;
			
			 // ! open a panel
			if ( tar.hasClass('open-panel') && tar.get('tagName') == 'A' ) {
			
				// stop
				e.halt();
			
				// open it in a panel
				this.panel.load( tar.get('href') ,{'openAfter':true});
			
			}
			else if (  tar.hasClass('fb-login') ) {

				// stop
				e.halt();
				
				// login	
				FB.login(function(response) {
				  if (response.session) {
				    if (response.perms) {
						window.location.href = tar.get('href');
				    } 
				    else {
						alert("You must grant proper permissions")
				    }
				  } else {
				    alert("Error with facebook login");
				  }
				}, {perms:'read_stream,publish_stream,offline_access,user_about_me,email,user_location,friends_location,user_events,friends_events'});				

			}
		
			
			// no tar
			if ( !tar ) { return; }			
		
		},
		
		// keydown
		keyup : function(e) {
		
			// target
			var tar = oTar = e.target;
			
			 // ! open a panel
			if ( tar.hasClass('edit-slug') ) {
			
				var slug = tar.get('value').replace(/ /g,'-').replace(/'/g,'');
					
				Y.one("#slug-container").set('innerHTML',slug);
				
				// validate slug
				this.validateSlug(slug);	
			
			}
			
			// no tar
			if ( !tar ) { return; }			
		
		},
		
		// mouse
		mouse : function(e,type) {
			
			// target
			var tar = oTar = e.target;
			
			// custom
			this.fire('BLT-base:mouse',e);
		
		},
		
		getUrl : function(url,params) {
        
			// qp
			var qp = [];
			
				// add 
				for ( var p in params ) {
					qp.push(p+"="+ encodeURIComponent(params[p]) );
				}
        
        	// do it 
        	return url + (url.indexOf('?')==-1?'?':'&') + qp.join('&');
        
        },
		

		getAjaxUrl : function(act,params) {
		
			// reurn
			return this.getUrl( BLT.Env.Urls.base+'ajax/'+act, params);
			
		},
		
		getXhrUrl : function(act,params) {
		
			// reurn
			return this.getUrl( BLT.Env.Urls.base+'xhr/'+act, params);
			
		},
		
		getParent : function(tar,g,max) {
       	       	
			// no tar
			if ( !tar )	{ return false; }
       	       	
       		// max
       		if ( !max ) { max = 10; }
        
            // local
            var gt = g;
           	var i = 0;            
           	var m = max;
            
            if ( typeof g == 'object' ) {
            
            	// current
            	if ( tar.get('tagName') == gt.tag.toUpperCase() ) { return tar; }
            
            	// reutrn
                return tar.ancestor(function(el){
                	if ( i++ > max ) { return false; }
					return (el.get('tagName') == gt.tag.toUpperCase()); }
				);
				
            }
            else {
            
            	// current
            	if ( tar.hasClass(gt) ) { return tar; }            
            
            	// moreve
                return tar.ancestor(function(el){ 
                	if ( i++ > max ) { return false; }                
                	return el.hasClass(gt); 
                });
                
            }
        },
        
        
        validateSlug : function(slug) { 
        	
        	// url
			var url = BLT.Obj.getUrl(BLT.Env.Urls.base+'trucks/validate-slug',{'.context':'xhr'});
        	
        	// params
			var params = {
				'method': 'GET',
				'context': this,
				'data': 'slug='+slug,
				'timeout': 10000,
				'on': {
					'failure': function() {
					//	window.location.href = reg_url;
					},
				 	'complete': function(id,o,a) {
						
						// get data
						var json = false;
						
						// try to parse
						//try {
							
							json = $j.parse(o.responseText);
								
							var slugContainer = Y.one("span#slug-result");	
																					
							if (json.validslug == 'good') {
								
								slugContainer.removeClass('bad');
								slugContainer.addClass('good');
								slugContainer.set('innerHTML','Available');
								
							
							} else { 
								
								slugContainer.removeClass('good');
								slugContainer.addClass('bad');
								slugContainer.set('innerHTML','Taken');
								
							}
							
						//}
						//catch (e) {}
						
						// need a good stat
						if ( !json || json.stat != 1 ) {
							return false;						
						}
						
						
					}
				}
			}        	
        	
        	// fire
			Y.io(url,params);
        
        },
        
		displayMap : function(div,address) {
			
			// make our map		
		    this.store.map = new google.maps.Map2(document.getElementById(div));

	    	// get geo code
			var geocoder = new GClientGeocoder();
	    	
	    	// geocode address
	    	geocoder.getLatLng(
	    		address,
	    		function(pt) {	    
					BLT.Obj.store.map.setCenter(pt, 13);
		 			var marker = new GMarker(pt);
  				    BLT.Obj.store.map.addOverlay(marker);
	    		}
	    	);
		
		}        
        
			
	}

	// we fire some custom events
	Y.augment(BLT.Base, Y.EventTarget);

};

// push on our init function
BLT.Env.yuiprereq.push(f);

// make a yui object
var y = YUI(BLT.Env.yuiconfig);

// apply with our prereqs
y.use.apply(y,BLT.Env.yuiprereq);
