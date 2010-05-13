
<?php
	if ( $error ) {
		echo "<div class='message error'>{$error}</div>";
	}
?>

<form method="post" action="<?php echo $action; ?>">
<input type="hidden" name="do" value="reg.submit">
<input type="hidden" name="reg.token" value="<?php echo $token; ?>">

<?php if (!$noForm) { ?>
<ul class="form reg">
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
			else if ( isset($field[1]['type']) AND $field[1]['type'] == 'hidden' ) {
				echo Forms::field($field[1]);
			}
		}
	?>
	<li class="buttons">
		<button type="submit">Sign Up</button>
	</li>
</ul>
</form>

<?php } ?>