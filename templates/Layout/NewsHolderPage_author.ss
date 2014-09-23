<div class="container">
	<div class="row col-xs-10">
		<div class="col-xs-12">
			<h1><%t NewsHolderPage.ITEMS_BY_AUTHOR "All items by " %>$CurrentAuthor.OriginalName.XML</h1>
		</div>
		<% if $allNews %>
		<% loop $allNews %>
		<div class="col-xs-4 $FirstLast">
			<% include SingleSummaryItem %>
		</div>
		<% end_loop %>
		<div class="clearfix"></div>
		<!--Setup the pagination-->
		<div class='row pagination pagination-sm'>
			<% if $allNews.NotFirstPage %>
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
				<a href="$Link" title='<%t NewsHolderPage.JUMPTO_PAGE "Jump to page" %> $PageNum'>$PageNum</a>
			</li>
			<% else %>
			<li class="disabled">
				<a>...</a>
			</li>
			<% end_if %>
			<% end_if %>
			<% end_loop %>
			<% if $allNews.NotLastPage %>
			<li>
				<a class='<%t NewsHolderPage.NEXT_PAGE "Next" %>' href="$allNews.NextLink"><%t NewsHolderPage.NEXT_PAGE "Next" %></a>
			</li>
			<% end_if %>
		</div>
		<% end_if %>
	</div>
	<!--Optional, include the sidebar with Archive overview-->
	<div id="sidebar" class="col-xs-2">
		<% include ArchiveOverview %>
	</div>
</div>
