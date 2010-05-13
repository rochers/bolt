<?php
	if ( $error !== false ) {
		echo "<div class='message error'>{$error}</div>";
	}
?>



<form method="post" action="<?php echo SELF; ?>">
<input type="hidden" name="do" value="forgot.submit">
<input type="hidden" name="forgot.token" value="<?php echo $token; ?>">
<ul class="form">
	<li><p>Please enter you Email Address below to reset your password.</p></li>
	<li>
		<label>
			<em>Email Address</em>
			<input type="text" name="f[email]" value="">
		</label>
	</li>

	<li class="buttons">
		<button type="submit">Continue</button>
	</li>
</ul>
</form>

