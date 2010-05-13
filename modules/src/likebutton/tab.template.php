<div class="like">

	<form method="post" class="direct" action="<?php echo URI; ?>ajax" enctype="multipart/form-data">
			
			<input type="hidden" name="do" value="like">	
			<input type="hidden" name="module" value="likebutton" />
			<input type="hidden" name="origin" value="<?php echo SELF; ?>" />
			
			<input type="hidden" name="asset" value="<?php echo $asset; ?>" />
			<input type="hidden" name="user" value="<?php echo $user; ?>" />
			
			<span id="like-wrap-<?php echo $asset; ?>">
				<?php if (empty($yourLike)) { ?>
					<a id="like-it-<?php echo $asset; ?>" class="like-it open-panel" href="<?php echo Config::url('login'); ?>">
						<input type="submit" class="submit-like" value=" " />
						<input type="hidden" class="logged" value="<?php echo Session::getLogged(); ?>" />
						<input type="hidden" class="like-type" value="tab" />
						Like It
					</a>
				<?php } else {	?>
					<div class="like-it">You Like It</div>
				<?php } ?>
			</span>
					
	</form>
	
	<em><span id="like-count-<?php echo $asset; ?>" class="like-count"><?php echo $likes->total;?></span> Like This</em>
	
	<?php if (empty($yourLike)) { ?>
			<script>
				BLT.add('l',function() { new BLT.Like({"type":"tab","asset":"<?php echo $asset; ?>","ajaxUrl":"<?php echo URI; ?>ajax"}) } ,'like');
			</script>
	<?php } ?>
	
</div>