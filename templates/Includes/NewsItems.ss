<div id="newsitems" class="row">
	<% loop NewsArchive(3) %> <%-- Check code/extensions/NewsExtension.php for configuration --%>
	<div class="col-xs-4 $FirstLast">
		<% if Impression %>
		<a href="$Link" class="impressionLink">
			<% with Impression %>$SetSize(50,50)<% end_with %>
		</a>
		<% end_if %>
		<% if Type == external %>
		<h2><a href='$External' target="_blank">$Title</a></h2>
		<% else_if Type == download %>
		<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
		<% else %>
		<h2><a href="$Link">$Title</a></h2>
		<% end_if %>
		<h3>$Author</h3>
		<i>$Published</i>
		<div class="row">
			<div class="col-xs-12">
				<% if Synopsis %>
				<p>$Synopsis</p>
				<% else %>
				<p>$Content.Summary</p>
				<% end_if %>
			</div>
			<% if Tags.Count > 0 %>
			<div class="col-xs-12">
				<% loop Tags %>
				<a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
				<% end_loop %>
			</div>
			<% end_if %>
		</div>
		<div class="col-xs-12 text-right">
			<a href="$Link" title="Read more about $Title"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a>
		</div>
        </div>
	<% end_loop %>
</div>