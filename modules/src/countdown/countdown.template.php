<div id="countdown-<?php echo $id; ?>" class="countdown">
	
	<div class="details">
		<span class="label">
			<?php echo $label; ?>
		</span>
		
			<?php echo $left; ?>
	</div>
	
</div>

<script>
BLT.add('l',function(){
	
		// checkout 
		BLT.Store.countdown = new BLT.Class.Countdown({'id':'<?php echo $id; ?>','endtime':'<?php echo $endtime; ?>'});
	
		
	});
</script>