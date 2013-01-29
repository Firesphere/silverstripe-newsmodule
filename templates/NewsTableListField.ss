<div id="$id" class="$CSSClasses field">
	<% if Print %><% else %>
		<% if Markable %>
			<% include TableListField_SelectOptions %>
		<% end_if %>
		<% include TableListField_PageControls %>
	<% end_if %>
	<table class="data">
		<thead>
			<tr>
			<% if Markable %><th width="16"><% if MarkableTitle %>$MarkableTitle<% else %>&nbsp;<% end_if %></th><% end_if %>
			<% if Print %>
				<% control Headings %>
				<th class="$Name">
					$Title
				</th>
				<% end_control %>
			<% else %>
			<% control Headings %>
				<th class="$Name">
				<% if IsSortable %>
					<span class="sortTitle">
						<a href="$SortLink">$Title</a>
					</span>
					<span class="sortLink <% if SortBy %><% else %>sortLinkHidden<% end_if %>">
					<% if SortDirection = desc %>
						<a href="$SortLink"><img src="cms/images/bullet_arrow_up.png" alt="<% _t('SORTDESC', 'Sort in descending order') %>" /></a>
					<% else %>
						<a href="$SortLink"><img src="cms/images/bullet_arrow_down.png" alt="<% _t('SORTASC', 'Sort in ascending order') %>" /></a>
					<% end_if %>
						</a>
						&nbsp;
					</span>
				<% else %>
					<span>$Title</span>
				<% end_if %>
				</th>
			<% end_control %>
			<% end_if %>
			<% if Can(delete) %><th width="18">&nbsp;</th><% end_if %>
                        <th width="18">Bovenaan</th>
                        <th width="18">Publiceren</th>
			</tr>
		</thead>

		<% if HasSummary %>
		<tfoot>
			<tr class="summary">
				<% include TableListField_Summary %>
			</tr>
		</tfoot>
		<% end_if %>

		<tbody>
			<% if HasGroupedItems %>
				<% control GroupedItems %>
					<% control Items %>
						<% include NewsTableListField_Item %>
					<% end_control %>
					<tr class="summary partialSummary">
						<% include NewsTableListField_Summary %>
					</tr>
				<% end_control %>
			<% else %>
				<% if Items %>
					<% control Items %>
						<% include NewsTableListField_Item %>
					<% end_control %>
				<% else %>
					<tr class="notfound">
						<% if Markable %><th width="18">&nbsp;</th><% end_if %>
						<td colspan="$Headings.Count"><i><% _t('NOITEMSFOUND','No items found') %></i></td>
						<% if Can(delete) %><td width="18">&nbsp;</td><% end_if %>
                                                <td width="18">Bovenaan</td><%-- do/undo force to frontpage --%>
                                                <td width="18">Publiceren</td><%-- do/undo force to frontpage --%>
					</tr>
				<% end_if %>
				<% if Can(add) %>
					$AddRecordAsTableRow
				<% end_if %>
			<% end_if %>
		</tbody>
	</table>
	<% if Print %><% else %><div class="utility">
		<% control Utility %>
			<span class="item"><a href="$Link">$Title</a></span>
		<% end_control %>
	</div><% end_if %>
</div>
<script type="text/javascript">
//<![CDATA[
    jQuery(function($){
        /**
         * first remove the original live-on-click
         */
	$('#right #Form_ResultsForm tbody td a:not(.deletelink,.downloadlink)').die('click');
        /**
         * now set the new live-on-click and exclude .toggleForceToFrontpage
         */
	$('#right #Form_ResultsForm tbody td a:not(.deletelink,.downloadlink,.toggleForceToFrontPage,.togglePublishNow)').live('click', function(){
            $(this).parent().parent().addClass('loading');
            var el = $(this);
            $('#ModelAdminPanel').fn('addHistory', el.attr('href'));
            $('#ModelAdminPanel').fn('loadForm', el.attr('href'));
            return false;
    	});
        /**
         * special handling for the toggleForceToFrontPage clicks
         */
        $('.toggleForceToFrontPage').live('click', function(event) {
            event.preventDefault();
            var elm = $(this);
            var parentRow = elm.parent().parent();
            parentRow.addClass('loading');
            $.ajax({
                url : $(this).attr('href'),
                type : 'GET',
                dataType : 'json',
                error : function(data) {
                },
                success : function(data) {
                    if (data.ForceToFrontPage == 1) {
                        parentRow.addClass('forced');
                    } else {
                        parentRow.removeClass('forced');
                    }
                    $('td.ForceToFrontPage', parentRow).text(data.ForceToFrontPageText);
                    parentRow.removeClass('loading');
                }
            });
            elm.blur();
        });
        /**
         * special handling for the togglePublish clicks
         */
        $('.togglePublishNow').live('click', function(event) {
            event.preventDefault();
            var elm = $(this);
            var parentRow = elm.parent().parent();
            parentRow.addClass('loading');
            $.ajax({
                url : $(this).attr('href'),
                type : 'GET',
                dataType : 'json',
                error : function(data) {
                },
                success : function(data) {
                    if (data.togglePublishNow == 1) {
                        parentRow.addClass('liveset');
                    } else {
                        parentRow.removeClass('liveset');
                    }
                    $('td.togglePublishNow', parentRow).text(data.togglePublishNowText);
                    parentRow.removeClass('loading');
                }
            });
            elm.blur();
        });
    });
//]]>
</script>