<form method="post" action="<?php echo SELF; ?>">
<input type="hidden" name="do" value="acct.submit">
<input type="hidden" name="acct.token" value="<?php echo $token; ?>">

<?php
	if ( $error ) {
		echo "<div class='message error'>{$error}</div>";
	}
?>

<ul class="form account">
	<?php
		foreach ( $form as $name => $field) {
			if ( $field[0] != false ) {
				echo "
					<li>
						<em>{$field[0]}</em>
						".Forms::field($field[1])."
					</li>
				";
			}
		}
	?>
	<li class="buttons">
		<button type="submit">Update</button>
	</li>
</ul>
</form>