<div class="row">
	<section id="tag" class="row col-xs-12">
	<% with Tag %>
		<% if $Impression %>
			<div class="col-xs-2">
				<a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a>
			</div>
			<div class="col-xs-10">
		<% else_if $Top.SiteConfig.DefaultImage %>
			<div class="col-xs-2">
				<a href="$Link" class="impressionLink col-xs-2">$Top.SiteConfig.DefaultImage.SetSize(50,50)</a>
			</div>
			<div class="col-xs-10">
		<% else %>
			<div class="col-xs-12">
		<% end_if %>
			<!--This is using the default, it should take the SiteConfig function into consideration. @todo: Use siteconfig over default.-->
			<h1><%t NewsHolderPage_tag.TAG "Tag" %>: <a href="{$Top.URLSegment}/tags">$Title</a></h1>
			<br />
			$Description
			<br />
			<a href="{$Top.URLSegment}/tags"><%t NewsHolderPage_tag.ALLTAGS "All tags" %>.</a>
			</div>
			<div class="col-xs-12 tag-socialbuttons newsitem-socialbuttons">
				<a href="https://twitter.com/share" class="twitter-share-button" data-via="{YOUR TWITTER ACCOUNT}" data-dnt="true">Tweet</a>
				<br />
				<div class="fb-like" data-href="$BaseHref{$Up.URLSegment}/show/$URLSegment" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
				<br />
			</div>
		</div>
	<% end_with %>
	</section>
</div>
<div class="clearfix"></div>
<div class="row">
	<section id="related_news" class="col-xs-12">
	<% loop Tag.News %>
		<article class="col-xs-4 $FirstLast">
			<% if Impression %>
				<a href="$Link" class="impression col-xs-2">
					<% with Impression %>$SetSize(50,50)<% end_with %>
				</a>
				<div class="col-xs-10">
			<% else %>
				<div class="col-xs-12">
			<% end_if %>
			<% if Type == external %>
					<h2><a href='$External' target="_blank">$Title</a></h2>
			<% else_if Type == download %>
					<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
			<% else %>
					<h2><a href="$Link">$Title</a></h2>
					<i><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></i>
					<% if Synopsis %>
						<p>$Synopsis</p>
					<% else %>
						<p>$Content.Summary</p>
					<% end_if %>
			<% end_if %>
			<% if Tags.Count > 0 %>
				<div class="col-xs-12">
					<% loop Tags %>
						<!--Note, don't forget this might need to be static!-->
						<a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
					<% end_loop %>
				</div>
			<% end_if %>
			<div class="col-xs-3 col-xs-offset-9">
				<a href="$Link" title="Read more about $Title"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a>
			</div>
		</article>
	<% end_loop %>
	</section>
</div>
<div class="clear"></div>
