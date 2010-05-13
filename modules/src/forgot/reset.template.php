<?php
	if ( $error !== false ) {
		echo "<div class='message error'>{$error}</div>";
	}
?>

<form method="post" action="<?php echo SELF; ?>">
<input type="hidden" name="do" value="forgot.submit">
<input type="hidden" name="forgot.token" value="<?php echo $token; ?>">
<input type="hidden" name="token" value="<?php echo p('token'); ?>">
<input type="hidden" name="rid" value="<?php echo p('rid'); ?>">
<ul class="form">
	<li>
		<label>
			<em>Email Address</em>
			<input type="text" name="f[email]" value="">
		</label>
	</li>
	<li>
		<label>
			<em>New Password</em>
			<input type="password" name="f[pword]" value="">
		</label>
	</li>
	<li class="buttons">
		<button type="submit">Continue</button>
	</li>
</ul>
</form>

