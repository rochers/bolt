<?php if ($fbLogin) { ?>
<div class="fb">
	<span id="log-in" class="fb-button">
	</span>
	<span class="label">Login with your Facebook Account</span>
</div>
<?php } ?>

<?php
	if ( $error !== false ) {
		echo "<div class='message error'>{$error}</div>";
	}
?>

<form method="post" action="<?php echo SELF; ?>">
<input type="hidden" name="do" value="login.submit">
<input type="hidden" name="login.token" value="<?php echo $token; ?>">
<ul class="form">
	<li>
		<label>
			<em>Email Address</em>
			<input type="text" name="f[email]" value="<?php echo $f['email']; ?>">
		</label>
	</li>
	<li>
		<label>
			<em>Password</em>
			<input type="password" name="f[pword]" value="">
			<div class="forgot"><a href="<?php echo Config::url('forgot'); ?>">Forgot Your Password?</a></div>
		</label>	
	</li>
	<li class="buttons">
		<button type="submit">Login</button>
	</li>
</ul>
</form>