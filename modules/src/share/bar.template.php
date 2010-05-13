<ul class="sharebar">
	<li>
		<a target="_blank" class="fbshare" name="fb_share" type="icon_link" href="http://www.facebook.com/sharer.php?u=<?php echo urlencode($shortUrl); ?>&t=<?php echo urlencode($title); ?>">Share on Facebook</a>
	</li>
	<li>
		<a target="_blank" class="twitter" href='<?php echo $twitterUrl; ?>'>Share On Twitter</a>
	</li>
	<li>
		<a class="email" href="mailto:?subject=<?php echo rawurlencode(html_entity_decode($title,ENT_QUOTES,'utf-8')); ?>&body=<?php echo rawurlencode($email); ?>">Email To Friend</a>
	</li>
</ul>