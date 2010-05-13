<span id="like-wrap-<?php echo $asset; ?>">
	<?php if (Session::singleton()->loged) { ?>
		
		<?php if (empty($yourLike)) { ?>
			<a id="like-it-<?php echo $asset; ?>" class="like-it" href="#">
		<?php } ?>
			<?php if (!empty($yourLike)) { echo 'You '; } ?>
			Like It (<span id="like-count-<?php echo $asset; ?>"><?php echo $likes->total; ?></span>)
			<input type="hidden" class="logged" value="<?php echo Session::getLogged(); ?>" />
			<input type="hidden" class="like-type" value="text" />
		<?php if (empty($yourLike)) { ?>
			</a>
		<?php } ?>
		
		<?php if (empty($yourLike)) { ?>
			<script>
				BLT.add('l',function() { new BLT.Like({"type":"text","asset":"<?php echo $asset; ?>","ajaxUrl":"<?php echo URI; ?>ajax"}) } ,'like');
			</script>
		<?php } ?>
				
	<?php } else { ?>
		
		<a class="open-panel" href="<?php echo URI; ?>login">
			Sign In to Like It (<?php echo $likes->total; ?>)
		</a>
		
	<?php } ?>
</span>	


