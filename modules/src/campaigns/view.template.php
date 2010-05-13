<?php

	// url
	$customer = "http://".getenv("platform_dashboard__customerHost");

?>
<h1><?php echo $campaign->name; ?></h1>

<div class="yui-gc">
	<div class="yui-u first">
	
		<div class="mod">
			<div class="hd"><h2>Recent PageView</h2></div>
			<div class="bd">
				<table class="report">
					<tr>
						<th>Time</th>		
						<th>Vistor</th>						
						<th class='l'>Page</th>
						<th class='l'>Referrer</th>						
					</tr>
					<?php
						foreach ( $today as $item ) {
							echo "
								<tr>
									<td>
										{$item->f_ts_ago}
										<div class='small gray'>".$item->date('ts',DATE_SHORT_FRM)."</div>										
									</td>
									<td><a href='".Config::url('campaign-visitor',array('guid'=>$item->guid))."'>{$item->ip}</a></td>
									<td class='l'>
										<a href='".Config::url('campaign-page',array('pid'=>$item->page_id))."'>{$item->title}</a>
										<div class='small gray'><strong>Url:</strong> <a target='_blank' href='{$item->page}'>{$item->page_parsed->path}</a></div>
									</td>
									<td>
							";
							
								if ( $item->referrer ) {
									echo "
										<a href=''>{$item->referrer_parsed->host}/".$item->short('referrer_parsed_path',20)."</a>
									";
								}
							
							echo "
									</td>
								</tr>
							";
						}
					?>
				</table>
			</div>
		</div>
	
	</div>
	<div class="yui-u">
		
		<div class="mod">
			<div class="hd"><h2>General</h2></div>
			<div class="bd">
				<ul class="info">
					<li>
						<em>Campaign</em>
						<div><?php echo $campaign->campaign; ?></div>
					</li>
					<li>
						<em>Medium</em>
						<div><?php echo $campaign->medium; ?></div>			
					</li>
					<li>
						<em>Source</em>
						<div><?php echo $campaign->source; ?></div>					
					</li>
					<li>
						<em>Content</em>
						<div><?php echo $customer.$campaign->content; ?></div>					
					</li>					
				</ul>
			</div>
		</div>
	
		<div class="mod">
			<div class="hd"><h2>Redirects</h2></div>
			<div class="bd">
				<ul class="info form">
					<li>
						<em>Dailyd</em>
						<div>
							<?php
								
								// url
								$url = $customer."/r/";
								
								// parts
								$p = array(
									$campaign->campaign,
									( $campaign->source ? $campaign->source : '-' ),									
									( $campaign->medium ? $campaign->medium : '-' ),
								);
																							
								// implode
								$url .= implode("/",array_filter($p,function($el){ return $el; } ));
	
								echo "<textarea>$url</textarea>";
													
							?>							
						</div>
					</li>				
				</ul>
			</div>
		</div>	
	
	</div>
</div>