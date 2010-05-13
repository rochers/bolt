<ul class="form">
	<?php
		foreach ( $form['fields'] as $f ) {
			echo "
				<li>
					<em>{$f[0]}</em>
					".Forms::field($f[1])."
				</li>
			";
		}
	?>
</ul>