<h1>
	Campaigns
	<a class="button" href="<?php echo Config::url('campaign-create'); ?>">Create a Campaign</a>	
</h1>

<div class="yui-g">
	<div class="yui-u first">
	
		<div class="mod">
			<div class="hd"><h2>Open Campaigns</h2></div>
			<div class="bd">
				<table class="report">
					<tr>
						<th>Id</th>
						<th>Name</th>
					</tr>
					<?php
						foreach ( $campaigns as $item ) {
							echo "
								<tr>
									<td>{$item->id}</td>
									<td><a href='".Config::url('campaign-view',array('id'=>$item->id))."'>{$item->name}</a></td>
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
			<div class="hd"><h2>Recent Visitors</h2></div>
			<div class="bd">
				<table class="report">
					<tr>
						<th class='l'>Page</th>
						<th>Time</th>			
						<th>Campaign</th>		
						<th>&nbsp;</th>	
					</tr>
					<?php
						foreach ( $today as $item ) {
							echo "
								<tr>
									<td class='l'>
										<a href=''>{$item->title}</a>
										<div class='small gray'><strong>Url:</strong>{$item->page_parsed->path}</div>
									</td>
									<td>{$item->f_ts_ago}</td>
									<td><a href='".Config::url('campaign-view',array('id'=>$item->campaign->id))."'>{$item->campaign->name}</a></td>
									<td><a href='".Config::url('campaign-visitor',array('guid'=>$item->guid))."'>more</a>
								</tr>
							";
						}
					?>
				</table>
			</div>
		</div>
		
	</div>
</div>
