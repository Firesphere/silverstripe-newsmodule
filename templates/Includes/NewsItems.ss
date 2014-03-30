<section id="latest_work" class="clearfix">
      <% loop NewsArchive(3) %> <%-- Check code/extensions/NewsExtension.php for configuration --%>
      <article class="one_third $FirstLast"><% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
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
			<div class="small">
			      <% loop Tags %>
			      <a href="$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
			      <% end_loop %>
			</div>
			<br />
		<% end_if %>
		<div class="col-xs-3 col-xs-offset-9">
			<a href="$Link" title="Read more about $Title"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a>
		</div>
        </article>
	<% end_loop %>
</section>