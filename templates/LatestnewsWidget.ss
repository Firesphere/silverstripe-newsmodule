<% if $latestNews %>
<div class='block row'>
	<ul class='newslist'>
		<% loop $latestNews %>
			<li class='col-xs-12'>
				<a href='$Link' class='$FirstLast'>
					<% if $Impression %>
					<div class='widget-news-image col-xs-2'>
						<% with $Impression %>
							$setSize(70,70)
						<% end_with %>
					</div>
					<% end_if %>
					<div class='widget-news-information col-xs-10'>
						<h4>$Title</h4>
						$Author<br />
						$PublishFrom.FormatI18N('%a %e %b')
					</div>
				</a>
			</li>
		<% end_loop %>
	</ul>
</div>
<% end_if %>