<tr id="record-$Parent.id-$ID"<% if HighlightClasses %> class="$HighlightClasses"<% end_if %>>
	<% if Markable %><td width="16" class="$SelectOptionClasses">$MarkingCheckbox</td><% end_if %>
	<% control Fields %>
            <% if Name == ForceToFrontPage %>
                <td class="field-$Title.HTMLATT $FirstLast $Name"><% if Value == 1 %><% _t('FORCE_TO_FRONTPAGE', 'Force to frontpage *NYT*') %><% end_if %></td>
            <% else %>
		<td class="field-$Title.HTMLATT $FirstLast $Name">$Value</td>
            <% end_if %>
	<% end_control %>
	<% control Actions %>
		<td width="16" class="action">
			<% if IsAllowed %>
			<a class="$Class" href="$Link"<% if TitleText %> title="$TitleText"<% end_if %>>
				<% if Icon %><img src="$Icon" alt="$Label" /><% else %>$Label<% end_if %>
			</a>
			<% else %>
				<span class="disabled">
					<% if IconDisabled %><img src="$IconDisabled" alt="$Label" /><% else %>$Label<% end_if %>
				</span>
			<% end_if %>
		</td>
	<% end_control %>
                <td width="18" class="action">
                    <a class="toggleForceToFrontPage" href="admin/news/News/$ID/toggleForceToFrontPage" title="<% _t('TOGGLE_FORCE_TO_FRONTPAGE', 'Toggle -Force to frontpage- *NYT*') %>"><img src="newsadmin/images/blank.gif" alt="Toggle" /></a>
                </td>
                <td width="18" class="action">
                    <a class="togglePublishNow" href="admin/news/News/$ID/togglePublish" title="<% _t('TOGGLE_PUBLISH', 'Publiceren/Depubliceren') %>"><img src="newsadmin/images/blank.gif" alt="Toggle" /></a>
                </td>
</tr>