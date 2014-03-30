<div class="row">
	<section id="tags" class="col-xs-12">
		<div class="cloud">
			<div class="tagCloud">
				<% loop allTags %>
					<a href="{$Top.URLSegment}/tag/$URLSegment" rel="$News.count()" >$Title</a>
				<% end_loop %>
			</div>
			<div class="news-socialbuttons"
				<a href="https://twitter.com/share" class="twitter-share-button" data-via="{YOUR TWITTER ACCOUNT}" data-dnt="true">Tweet</a>
				<br />
				<div class="fb-like" data-href="$BaseHref{$URLSegment}" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
				<br />
			</div>
		</div>
	</section>
</div>
<div class="clearfix"></div>