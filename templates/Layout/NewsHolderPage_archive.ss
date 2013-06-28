<div class="wrapper row2">
  <div id="container">
    <div id="homepage">
      <section id="latest_work">
	      <% include ArchiveOverview %>
<% if getArchive %>
      <% loop getArchive %>
      <article class="one_third $FirstLast <% if IsThird %>First<% end_if %>">
	      	      <div class="articleContainer">
	      <% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
		<% if Type == external && Top.SiteConfig.ReturnExternal %>
			<h2><a href='$External' target="_blank">$Title</a></h2>
		<% else_if Type == download && Top.SiteConfig.ReturnExternal %>
			<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.ss.DOWNLOADABLE "Download" %>)</a></h2>
		<% else %>
			<h2><a href="$Link">$Title</a></h2>
		<% end_if %>
	  <h3><%t NewsHolderPage.ss.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></h3>
	  <% if Synopsis %>
		<p>$Synopsis</p>
	  <% else %>
		<p>$Content.Summary</p>
	  <% end_if %>
	  <% if not Top.SiteConfig.ReturnExternal %>
		<footer class="more"><a href="$Link">Read More &raquo;</a></footer>
	  <% end_if %>
	</div>
	  <% if Tags.Count > 0 %>
	  <br />
	  <div class="small">
		<% loop Tags %>
		<a href="{$Top.URLSegment}/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
		<% end_loop %>
	  </div>
	  <br />
	  <% end_if %>
        </article>
	      <% end_loop %>
	      <% end_if %>
      </section>

    </div>
        <div class="clear"></div>
	      <div class='pagination'>
<% if getArchive.MoreThanOnePage %>
    <% if getArchive.NotFirstPage %>
        <a class="prev" href="$allNews.PrevLink">Prev</a>
    <% end_if %>
    <% loop getArchive.PaginationSummary %>
        <% if CurrentBool %>
            $PageNum
        <% else %>
            <% if Link %>
                <a href="$Link">$PageNum</a>
            <% else %>
                ...
            <% end_if %>
        <% end_if %>
        <% end_loop %>
    <% if getArchive.NotLastPage %>
        <a class="next" href="$allNews.NextLink">Next</a>
    <% end_if %>
<% end_if %>
	      </div>
  </div>
</div>
