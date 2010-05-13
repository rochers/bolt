<div id="comments-<?php echo $id; ?>" class="comments">
		
		<ul>
		<?php 

			foreach ($comments as $c) { 
				
				$class = '';
				$extra = '';
				
				if ($c->parent_comment) { 
					$class = 'reply';
					$extra = '<span class="nub"></span>';
				}
								
				echo '<li class="'.$class.'">
						'.$extra.'
						<span class="text">'.nl2br($c->comment).'</span> 
						<span class="credit"><span class="txt">'.$c->user->nick;
				
				if ($c->role == 'M') { 
					
					echo ', on behalf of ' . $merchant;
				
				}
				
				echo '</span> posted 
					  <span class="date">'.ago($c->created_ts).'</span> 
					  </span>
					</li>';
		
			}
				
		 ?>
		 </ul>
		
		<form method="post" class="direct" action="<?php echo URI; ?>ajax" enctype="multipart/form-data">
			
		<?php if (Session::getLogged()) { ?>
			
			<h2>Post a Comment</h2>
			
			<input type="hidden" name="do" value="add">	
			<input type="hidden" name="module" value="comments" />
			<input type="hidden" name="origin" value="<?php echo SELF; ?>" />
			
			<input type="hidden" name="asset" value="<?php echo $asset; ?>" />
						
			<textarea name="comment" class="writebox unselected"></textarea>
			
			<div class="ft">
				
				<input type="submit" class="submit-comment" value="Post Comment" />
				<div class="seenas">You will be seen as '<?php echo Session::getUser()->nick; ?>'</div>
			
			</div>
			
		<?php } else { ?>
		
			<h2>Please <a href="<?php echo Config::url('login'); ?>">sign in</a> to comment.</h2>
			
		<?php } ?>
		
		</form>
		
</div>

<script>
	BLT.add('l',function() { new BLT.Comments({'id':'<?php echo $id; ?>'}) } ,'comments');
</script>