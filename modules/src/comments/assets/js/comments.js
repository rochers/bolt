YUI.add("bolt-comments",function(Y) {

	// shortcuts
	var $ = Y.get, $j = Y.JSON;

	// base 
	BLT.Comments = function(args) {
		this.init(args);
	}

	// base prototype
	BLT.Comments.prototype = {
		
		// args
		args : {},
		
		// init 
		init : function(args) {
			
			// args
			this.args = args;  
			
			if (!$('#comments-' + this.args.id)) { return; }
			
			// attach listeners
			$('#comments-' + this.args.id).on('click',this.click,this);
			//$('.comments').on('mouseover',this.mouse,this);			
			//$('.comments').on('mouseout',this.mouse,this);
			//$('.comments').on('keyup',this.keyup,this);
	        	                                    
		},
		
		// click
		click : function(e) { 
		
			// tar
			var tar = e.target;
			
			// fire
			this.fire('comments:click',{'target':tar,'event':e});
			
			// close
			if ( tar.hasClass('writebox') ) {
				
				this.setupCommentBox(tar);
				
			} 
			
		},
		
		
		setupCommentBox : function(tar) { 
		
			if ( tar.hasClass('unselected') ) {
				
				tar.removeClass('unselected');
				tar.addClass('selected');
				tar.on('blur',this.restoreCommentBox,this);
								
			} 
			
				
		
		},
		
		
		restoreCommentBox : function(e) { 
		
			// tar
			var tar = e.target;
			
			if (tar.get('value') == '') { 
				
				tar.removeClass('selected');
				tar.addClass('unselected');
			
			}
		
		}		
			
	} 
	
	// we fire some custom events
	Y.augment(BLT.Comments, Y.EventTarget);

});