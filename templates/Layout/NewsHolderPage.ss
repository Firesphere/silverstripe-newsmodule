<div class="row col-xs-10">
	<section class="col-xs-12">
	<% if $allNews %>
		<% loop $allNews %>
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
			<% if $Type == external && Top.SiteConfig.ReturnExternal %>
				<h2><a href='$External' target="_blank">$Title</a></h2>
			<% else_if $Type == download && Top.SiteConfig.ReturnExternal %>
				<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
			<% else %>
				<h2><a href="$Link">$Title</a></h2>
			<% end_if %>
				<h3><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></h3>
			<% if $Synopsis %>
			      <p>$Synopsis</p>
			<% else %>
			      <p>$Content.Summary</p>
			<% end_if %>
			<% if not $Top.SiteConfig.ReturnExternal %>
			      <div class="more"><a href="$Link"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a></div>
			<% else %>
			      <div class="more"><a href="$Link"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a></div>
			<% end_if %>
			<% if $Tags.Count > 0 %>
			<br />
			<div class="col-xs-12">
			      <% loop Tags %>
			      <a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
			      <% end_loop %>
			</div>
			<br />
			<% end_if %>
		  </article>
		<% end_loop %>
	<% end_if %>
	</section>
	<!--Optional, include the sidebar with Archive overview-->
	<div id="sidebar" class="col-xs-2">
		<% include ArchiveOverview %>
	</div>
</div>
<div class="clear"></div>
<!--Setup the pagination-->
<div class='row pagination pagination-sm'>
	<% if allNews.NotFirstPage %>
		<li>
			<a class='<%t NewsHolderPage.PREVIOUS_PAGE "Previous" %>' href="$allNews.PrevLink"><%t NewsHolderPage.PREVIOUS_PAGE "Previous" %></a>
		</li>
	<% end_if %>
	<% loop $allNews.PaginationSummary %>
		<% if $CurrentBool %>
			<li class="active">
				<a>$PageNum</a>
			</li>
		<% else %>
			<% if $Link %>
				<li>
					<a href="$Link" title='<%t NewsHolderPage.JUMPTO_PAGE, "Jump to page" %> $PageNum'>$PageNum</a>
				</li>
			<% else %>
				<li class="disabled">
					<a>...</a>
				</li>
			<% end_if %>
		<% end_if %>
	<% end_loop %>
	<% if $allNews.CurrentPage < $allNews.TotalPages %>
	    <li>
		<a class='<%t NewsHolderPage.NEXT_PAGE "Next" %>' href="$allNews.NextLink"><%t NewsHolderPage.NEXT_PAGE "Next" %></a>
	    </li>
	<% end_if %>
</div>
