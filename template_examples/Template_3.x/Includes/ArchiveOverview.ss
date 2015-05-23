<% if getArchiveList %>
    <div class="ArchiveList">
        <ul>
			<% loop getArchiveList.GroupedBy(YearCreated) %>
                <li class="Archive $FirstLast">
                    <a href="$Top.Link(archive)/$YearCreated">$YearCreated</a>
					<% if Children %>
                        <ul class="Children">
							<% loop Children.GroupedBy(MonthCreated) %>
                                <li class="$FirstLast <% if Middle %>Middle<% end_if %>">
                                    <a href="$Top.Link(archive)/{$Up.Up.YearCreated}/$MonthCreated">$Children.First.PublishFrom.FormatI18N("%B")</a>
									<% if Middle || First %>
                                        &nbsp;|&nbsp;
									<% end_if %>
                                </li>
							<% end_loop %>
                        </ul>
					<% end_if %>
                </li>
			<% end_loop %>
        </ul>
    </div>
<% end_if %>
