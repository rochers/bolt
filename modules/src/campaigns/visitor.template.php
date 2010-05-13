<h1><?php echo $visits->item(0)->ip; ?></h1>

<div class="yui-gc">
	<div class="yui-u first">
		<?php
			
			
			foreach ( $campaings as $c ) {	
				echo "
					<div class='mod toggle closed'>
						<div class='hd'><h2>".$c[0]->campaign->name."</div>
						<div class='bd'>
							<table class='report'>
				";
				
					// pv
					$session = \dao\gertrude::compileSessions($c);
				
					foreach ( $session as $item ) {		
													
						// get session time
						$start = array_slice($item,-1);					
						
						echo "<tr><th colspan='5' class='l'>".date(DATE_SHORT_FRM,$start[0]->ts)."</th></tr>";
					
						// print each session
						foreach ( $item as $s ) {
							echo "
								<tr>
									<td>".$s->date('ts',TIME_FRM)."</td>
									<td>
										<a href=''>{$s->title}</a>
									</td>
								</tr>
							";
						}
					
					}
				
				
				echo "
							</table>
						</div>
					</div>
				";
			}
		
		?>
	</div>
	<div class="yui-u">
	
	</div>
</div>