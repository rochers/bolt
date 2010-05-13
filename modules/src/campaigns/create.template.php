<?php 
	$f = $form['fields'];

?>

<h1>Create a Campaign</h1>

<div class="yui-gc">
	<div class="yui-u first">

		<?php
			if ( $error ) {
				echo "<div class='message error'><ul>{$error}</ul></div>";
			}
		?>	
	
		<form method="post" action="<?php echo SELF; ?>">
			<input type="hidden" name="do" value="submit">
			
			<div class="mod">
				<div class="hd"><h2>About the Campaign</h2></div>
				<div class="bd">
					<ul class="form">
						<li>
							<em>Name</em>
							<?php echo Forms::field($f['name'][1]); ?>
						</li>
						<li>
							<em>Campaign</em>
							<?php echo Forms::field($f['campaign'][1]); ?>
						</li>
						<li>
							<em>Source</em>
							<?php echo Forms::field($f['source'][1]); ?>
						</li>
						<li>
							<em>Medium</em>
							<?php echo Forms::field($f['medium'][1]); ?>
						</li>
						<li>
							<em>Description</em>
							<?php echo Forms::field($f['desc'][1]); ?>
						</li>
					</ul>
				</div>
			</div>
			
			<div class="mod">
				<div class="hd"><h2>Content</h2></div>
				<div class="bd">
					<ul class="form">
						<li>
							<em>URL</em>
							<?php echo Forms::field($f['content'][1]); ?>
						</li>
					</ul>
				</div>
			</div>
			
			<ul class="form">
				<li class="buttons">
					<button type="submit">Create</button>
				</li>
			</ul>
	
		</form>
	
	</div>
</div>