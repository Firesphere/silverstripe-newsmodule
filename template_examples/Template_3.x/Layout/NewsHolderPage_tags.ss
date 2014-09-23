<div class="wrapper row1">
	<section id="shout" class="clear">
		<div class="cloud">
			<div class="tagCloud">
				<% loop Tags %>
				<a href="{$Top.URLSegment}/tag/$URLSegment" rel="$News.count()" >$Title</a>
				<% end_loop %>
			</div>
			<a href="https://twitter.com/share" class="twitter-share-button" data-via="{YOUR TWITTER ACCOUNT}" data-dnt="true">Tweet</a>
			<br />
			<div class="fb-like" data-href="$BaseHref{$URLSegment}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
			<br />
		</div>
	</section>
	<div class="clear"></div>
</div>