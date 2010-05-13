YUI.add("bolt-panel",function(Y) {

	// shortcuts
	var $ = Y.get, $j = Y.JSON;

	// base 
	BLT.Panel = function(args) {
		this.init(args);
	}

	// base prototype
	BLT.Panel.prototype = {
		
		// args
		args : {},
		
		// init 
		init : function(args) {
			
			// args
			this.args = args;
		
			// object
			this.obj = new Y.Overlay({
				"centered": true,
				"bodyContent": "",
				"zIndex": 100
			});
			
			// add our master class			
			if ( args && args.type != 'simple' ) {
			
				this.obj.get('boundingBox').append("<div class='back'></div><a class='close-panel'>close</a>");
				this.obj.get('boundingBox').addClass("panel");	
				
				// content
				this.obj.get('contentBox').append("<div class='loading_mask'></div><div class='loading_ind'></div>");
				
			}
			
			// add class
			if ( args && args['class'] ) {
				for ( var c in args['class'] ) {
					this.obj.get('boundingBox').addClass(args['class'][c]);
				}
			}
			
			// render
			this.obj.render("#doc");
			
			// hide
			this.obj.hide();
			
			// click
			this.obj.get('boundingBox').on('click',this.click,this);
			
			// events to publish
			this.publish('panel:click');
			this.publish('panel:open');
			this.publish('panel:close');			
			this.publish('panel:submit');			
			this.publish('panel:beforeload');			
			this.publish('panel:afterload');				
			
			// watch the xy change 
			this.obj.after('xyChange',function(e){
			
				// get the new xy
				var xy = e.newVal;
			
				// if the x is - reset to 10
				if ( xy[1] < 10 ) {		
					this.obj.move([xy[0],20]);
				}
			
			},this);		
		
			// scroll me
			Y.on('scroll',function(){
			
				// center
				this.obj.centered();
			
			},document,this);
		
		},
		
		// click
		click : function(e) { 
		
			// tar
			var tar = e.target;
			
			// fire
			this.fire('panel:click',{'target':tar,'event':e});
			
			// close
			if ( tar.hasClass('close-panel') ) {
				this.close();
			}
			
		},
		
		// open
		open : function() {
			
			// if modal
			if ( this.args.modal ) {
				
				if ( !Y.get('#panel-modal') ) {
					Y.get("body").insert("<div id='panel-modal' style='display:none'></div>");
				}			

				// set it 
				$("#panel-modal").setStyles({'opacity':0,'display':'block','height':Y.one(window).get('docHeight')+'px'});
			
				// fade them out
				var a = new Y.Anim({
			        node: $('#panel-modal'),
			        from: {
			        	'opacity': 0
			        },		        
			        to: {
						'opacity': .6
	        		},
	        		"duration": .5
				});				
				
				a.run();
				
			}
			
			// fire
			this.fire('panel:open');
		
			// open
			this.obj.show();
			
		},
		
		close : function(args) {
		
			// fire
			this.fire('panel:close',args);	
			
			// if modal
			if ( this.args.modal ) {		
			
				// fade them out
				var a = new Y.Anim({
			        node: $('#panel-modal'),
			        to: {
						'opacity': 0
	        		},
	        		"duration": .2
				});				
			
				a.on("end",function(){
					// set it 
					$("#panel-modal").setStyles({'opacity':0,'display':'none'});								
				});
				
				a.run();
			
				
			}				

			// load
			BLT.execute('u');
		
			// hide
			this.obj.hide();
			
		},
	
		// submit
		submit : function(e) { 
			
			// get target
			var tar = e.target;
			
			// has loading
			if ( tar.hasClass('loading') ) {
				return;
			}
			
			// loading
			tar.addClass('loading');
			
			// get the action
			var url = tar.getAttribute('x-action');
						
			// fire
			this.fire('panel:submit');			

			// get the form
			this.load(url,{'form':tar}); 
			
		},
	
		// load
		load : function(url,args) {
		
			// loading
			this.obj.get('boundingBox').addClass('loading');
		
			// fire
			this.fire('panel:beforeload');		
			
			// url
			var url = BLT.Obj.getUrl(url,{'.context':'xhr'});	
				
			// reg urk
			var reg_url = url;
					
			// params
			var params = {
				'method': 'GET',
				'context': this,
				'arguments': args,
				'timeout': 10000,
				'on': {
					'failure': function() {
					//	window.location.href = reg_url;
					},
				 	'complete': function(id,o,a) {
						
						// get fata
						var json = false;
						
						// try to parse
						try {
							json = $j.parse(o.responseText);
						}
						catch (e) {}
						
						// need a good stat
						if ( !json || json.stat != 1 ) {
						//	window.location.href = reg_url; return;						
						}
						
						// not loading
						this.obj.get('boundingBox').removeClass('loading');
						
						// check for special actions
						if ( json['do'] ) {
							if ( json['do'] == 'redi' ) {
								this.close();
								window.location.href = json.url; return;
							}
							else if ( json['do'] == 'error' ) {
								
								//remove loading class to allow resubmit
								Y.one(args.form).removeClass('loading');
								
								return;
							
							}
							else if ( json['do'] == 'login' ) {
								BLT.Obj.login(json.args); return;
							}
							else if ( json['do'] == 'load' ) {
													
								// load a page
								this.load(json.url+'&.context=xhr',{'openAfter':true}); return;
								
							}
							else if ( json['do'] == 'close' ) {
								this.close(json.args); return;
							}
							else if ( json['do'] == 'refresh' ) {
							
								// window
								window.location.href = window.location.href;
								
							}
						}
						
						// set it 
						this.obj.set('bodyContent',json.html);					
					
						// if bootstrap
						if ( json.bootstrap ) {
						
							// header content
							this.obj.set('headerContent',json.bootstrap.t);
							this.obj.get('bodyContent').addClass(json.bootstrap.c);
							
							// boot me
							if ( json.bootstrap.js ) {
								for ( var el in json.bootstrap.js ) {
									eval(json.bootstrap.js[el]);
								}
							}
							
						}
						
						// look for forms in the head content
						this.obj.get('boundingBox').all('form').each(function(el){						
							
							//don't do it if it's a direct post
							if (!el.hasClass('direct')) {
								
								// get attr
								var action = el.getAttribute('action');
								
								// reset it 
								el.setAttribute('x-action', action);
								el.setAttribute('action','#');
								el.setAttribute('method','get');										
															
									// attach to submit
									el.on('submit',function(e){	
										e.halt(); this.submit(e);								
									},this);
								
							}
							
						},this);
						
						this.obj.centered();
						
						// fire
						this.fire('panel:afterload');						
						
						// load
						BLT.execute('l');
						
						// open
						if ( a && a.openAfter ) {
							this.open();
						}			 		
						
					}
				}			
			};
			
			// form
			if (args && args.form) {
				params['form'] = { 'id': args.form };
				params['method'] = 'POST';
			}
		
			// fire
			Y.io(url,params);
		
		}
	
	} 
	
	// we fire some custom events
	Y.augment(BLT.Panel, Y.EventTarget);

});
