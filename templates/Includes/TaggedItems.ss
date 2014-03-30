<article class="col-xs-4 $FirstLast">
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
	<% if Type == external %>
		<h2><a href='$External' target="_blank">$Title</a></h2>
	<% else_if Type == download %>
		<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
	<% else %>
		<h2><a href="$Link">$Title</a></h2>
	<% end_if %>
			<h3>$Author</h3>
			<i><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></i>
			<% if Synopsis %>
				<p>$Synopsis</p>
			<% else %>
				<p>$Content.Summary</p>
			<% end_if %>
			<% if Tags.Count > 0 %>
			<br />
			</div>
				<div class="col-xs-12">
					<% loop Tags %>
				<!--		Note, don't forget this might need to be static!-->
						<a href="$Top.URLSegment/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
					<% end_loop %>
				</div>
			<br />
			<% end_if %>
			<div class="more"><a href="$Link"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a></div>
		</div>	  
</article>
