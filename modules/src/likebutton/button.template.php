<div class="like">

	<em><?php echo $likes->total; if ( $likes->total == 1) { echo ' Person Likes'; } else { echo ' People Like'; }  ?> This Deal</em>

	<form method="post" class="direct" action="<?php echo URI; ?>ajax" enctype="multipart/form-data">
			
			<input type="hidden" name="do" value="like">	
			<input type="hidden" name="module" value="likebutton" />
			<input type="hidden" name="origin" value="<?php echo SELF; ?>" />
			
			<input type="hidden" name="asset" value="<?php echo $asset; ?>" />
			<input type="hidden" name="user" value="<?php echo $user; ?>" />
													
			<input type="submit" class="submit-like" value="Like It" />
					
	</form>

</div>