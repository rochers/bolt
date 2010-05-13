<div class="expanded-share">
	
	<div class="left">
		<h3>Email To Friends</h3>
		
		<?php if (isset($emailed) AND $emailed) { ?>
		
		<div class="thanks">
			Thanks! Your emails were sent.
		</div>
		
		<a href="<?php $parts = explode('?',SELF); echo $parts[0]; ?>">Send More Emails &#187;</a>
		
		<?php } else { ?>
		
		<form id="send-email-form" class="email" method="post">	
			
			<input type="hidden" name="do" value="bolt-share-send-emails">	
			<input type="hidden" name="url" value="<?php echo $url; ?>" />
			<input type="hidden" name="origin" value="<?php echo SELF; ?>" />
			<input type="hidden" name="name" value="<?php echo $name; ?>" />
						
			<ul class="referrals">
				<li>Email 1: <input type="text" name="email1" /></li>
				<li>Email 2: <input type="text" name="email2" /></li>
				<li>Email 3: <input type="text" name="email3" /></li>
				<li>Email 4: <input type="text" name="email4" /></li>
				<li><input onclick="this.disabled=true; this.value='Sending...one moment'; document.getElementById('send-email-form').submit();" class="email" type="submit" value="Send To My Friends" /></li>
			</ul>
		</form>
		
		<?php } ?>
		
	</div>
	
	<div class="right">
		
		
		<meta property="og:title" content="<?php echo $title; ?>"/>
		<meta property="og:site_name" content="DailyD"/>
		<meta property="og:image" content="<?php echo $image; ?>"/>
			
		<ul class="social">
			<li>
				<h3>Facebook</h3>
				<iframe src="http://www.facebook.com/plugins/like.php?href=<?php echo urlencode($url); ?>&title=<?php echo rawurlencode($title); ?>layout=standard&amp;show_faces=true&amp;width=450&amp;action=recommend&amp;colorscheme=light" scrolling="no" frameborder="0" allowTransparency="true" style="border:none; overflow:hidden; width:450px; height:80px"></iframe>
			</li>
			<li>
				<h3>Twitter</h3>
				<form target="_blank" method="get" action="http://twitter.com/home">
					<textarea name="status" class="tweet"><?php echo $twitter; ?></textarea>
					<input class="twitter" type="submit" value="Tweet!" />
				</form>
			</li>
		</ul>
				
	</div>
	
</div>