YUI.add("bolt-form",function(Y) {
	
	// onload
	// BLT.add('l',function(){ var form = new BLT.Form(); });
	
	// shortcuts
	var $ = Y.get, $j = Y.JSON;

	// base 
	BLT.Form = function(args) {
		this.init(args);
	}

	// base prototype
	BLT.Form.prototype = {
		
		// args
		args : {},
		
		// init 
		init : function(args) {
			
			// attach listeners
			$('body').on('focus',this.focus,this);
			
			// attach listeners
			$('body').on('keyup',this.keyup,this);
			
			// args
			this.args = args;
			
			// field management
			this.fields = new Array();
			
			// animation duration
			this.animDuration = .25;
		
		},
		
		// click
		focus : function(e) { 
		
			// tar
			var tar = e.target;
			
			// reset all field states to make it interactive
			this.resetFields();
						
			// close
			if ( labelFor = BLT.Obj.getParent(tar,'clearField') ) {
				
				this.manageFieldState(labelFor);
				
			}
			
		},
		
		// keyup
		keyup : function(e) { 
		
			// tar
			var tar = e.target;
			
			// reset all field states to make it interactive
			this.resetFields();
						
			// close
			if ( labelFor = BLT.Obj.getParent(tar,'clearField') ) {
				
				this.manageFieldState(labelFor);
				
			}
		
		
		
		},
		
		
		manageFieldState : function(tar) {
					
			// get the id of the field
			var id = tar.get('id');
			
			// set id of field, if it doesn't exist (we need this for management of state)
			if (!id) { id = tar.set('id',Y.guid()).get('id'); }
						
			// get the label and span element for the field so we can animate
			var labelSpan = Y.one('#'+id+ ' span');
			
			// check to see if the field is faded already, if not, 
			if (!labelSpan.hasClass('faded')) { 
			
				// we're going to fade this field out because it looks hot
				var fieldAnim = new Y.Anim({
			        node: labelSpan,
			        duration: this.animDuration,
			        to: {
			            opacity: .5
	        		}
				});

				fieldAnim.on('end', function() {
    				fieldAnim.get('node').addClass('faded');
				});
			
				fieldAnim.run();
				
				//get width of label
				var labelWidth = parseInt(labelSpan.getStyle('width'),10); 
				
				//get width of field
				var fieldWidth = parseInt(Y.one('#'+id+ ' input').getStyle('width'),10); 
				
				// calc new left
				var leftMove = fieldWidth-labelWidth-7;
				
				if (leftMove > 0) { 
								
					// we're going to move this label to the right it looks hot
					var posAnim = new Y.Anim({
				        node: labelSpan,
				        duration: this.animDuration,
				        to: {
				            left: leftMove
		        		}
					});
					
					posAnim.run();
				
				} 				
			
			}
			
			// get the value of the field
			var fieldVal = Y.one('#'+id+ ' input').get('value');
			
			// if the value is set, let's hide the label because we don't want to overlap
			if (fieldVal != '') { 
				
				//labelSpan.addClass('hidden');
			
			} else { 
				
				labelSpan.removeClass('hidden');
			
			}
			
			
		
		},
		
		resetFields : function() {
				
			Y.all('.clearField').each( function(tar) {
				
				// get the id of the field
				var id = tar.get('id');
								
				// set id of field, if it doesn't exist (we need this for management of state)
				if (!id) { id = tar.set('id',Y.guid()).get('id'); }
							
				// get the label and span element for the field so we can animate
				var labelSpan = Y.one('#'+id+ ' span');
				
				// check to see if the field is faded already, if not, 
				if (labelSpan.hasClass('faded') && !labelSpan.hasClass('hidden')) { 
				
					// we're going to fade this field out because it looks hot
					var fieldAnim = new Y.Anim({
				        node: labelSpan,
				        duration: this.animDuration,
				        to: {
				            opacity: 1
		        		}
					});
	
					fieldAnim.on('end', function() {
	    				fieldAnim.get('node').removeClass('faded');
					});
				
					fieldAnim.run();
					
				}
					
					// get value
					var fieldVal = Y.one('#'+id+ ' input').get('value'); 
					
					if (fieldVal == '') { 
					
						// we're going to move this label to the left it looks hot
						var posAnim = new Y.Anim({
					        node: labelSpan,
					        duration: this.animDuration,
					        to: {
					            left: 0
			        		}
						});
						
						posAnim.run();
					
					} else {
					
						//get width of label
						var labelWidth = parseInt(labelSpan.getStyle('width'),10); 
						
						//get width of field
						var fieldWidth = parseInt(Y.one('#'+id+ ' input').getStyle('width'),10); 
						
						// calc new left
						var leftMove = fieldWidth-labelWidth-7;
						
						if (leftMove > 0) { 
										
							// we're going to move this label to the right it looks hot
							var posAnim = new Y.Anim({
						        node: labelSpan,
						        duration: this.animDuration,
						        to: {
						            left: leftMove
				        		}
							});
							
							posAnim.run();
						
						}
					
					}
					
				
			},this);		
		
		},
		
		
		displayError : function(element,error) { 
		
			var overlay = Y.one('.error_overlay');
			
			if (overlay && element) { 
				
				var coordinates = element.getXY();
				
				var elementWidth = parseInt(element.getComputedStyle('width'),10);
				
				// show it
				overlay.removeClass('hidden');
				
				// set the error msg
				Y.one('.error_overlay .container').set('innerHTML',error);
				
				// get width of new overlay
				var overlayWidth = parseInt(overlay.getComputedStyle('width'),10);
				
				// get height of new overlay
				var overlayHeight = parseInt(overlay.getComputedStyle('height'),10);
								
				// move the overlay
				overlay.setXY([coordinates[0] - (overlayWidth/2) + (elementWidth/2.2), coordinates[1] - (overlayHeight + 36)]);
				
							
			}
		
		},
		
		hideError : function() { 
		
			Y.one('.error_overlay').addClass('hidden');
			
		}
			
	} 
	
	// we fire some custom events
	Y.augment(BLT.Form, Y.EventTarget);

});
