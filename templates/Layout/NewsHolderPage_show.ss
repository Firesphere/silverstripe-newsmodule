<div class="container">
    <div id="newsitem" class="row">
		<% with $current_item %>
            <div class="col-xs-12">
				<% if $Impression %>
                    <div class="col-xs-2">
                        <a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a>
                    </div>
				<% else_if $Top.SiteConfig.DefaultImage %>
                    <div class="col-xs-2">
                        <a href="$Link" class="impressionLink col-xs-2">$Top.SiteConfig.DefaultImage.SetSize(50,50)</a>
                    </div>
				<% end_if %>
                <h1>$Title</h1>

                <h3><%t NewsHolderPage.DATEPUBLISH "{date} by " date=$Published %><a href='$AuthorHelper.Link'
                                                                                     title='$Author'>$Author</a></h3>
                <br/>

                <div class="content col-xs-12">
					<% if $Content %>
						$Content
					<% else %>
						$Synopsis
					<% end_if %>
					<% if $Type == External %>
                        <a href="$External" target="_blank">$Title</a>
					<% else_if $Type == Download %>
                        <a href="$Download.Link">$Title</a>
					<% end_if %>
                </div>
				<% if $Tags.Count > 0 %>
                    <div class="col-xs-12">
						<% loop Tags %>
                            <a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
						<% end_loop %>
                    </div>
				<% end_if %>
                <!--Example sharing buttons!-->
                <div class="col-xs-12 newsitem-socialbuttons">
                    <br/>
                    <a href="https://twitter.com/share" class="twitter-share-button"
                       data-via="$SiteConfig.TwitterAccount" data-dnt="true">Tweet</a>
                    <br/>

                    <div class="fb-like" data-href="$BaseHref{$Link}" data-send="false" data-layout="button_count"
                         data-width="100" data-show-faces="false" data-font="segoe ui"></div>
                    <br/>
                </div>

				<% if $AllowComments %>
                    <hr/>
					<% if $getAllowedComments %>
						<% include CommentList %>
					<% end_if %>
                    <div class="col-xs-12">
						$Top.CommentForm
                    </div>
				<% end_if %>
            </div>
		<% end_with %>
    </div>
</div>
<%-- If you want newsitems below this, include NewsItems.ss --%>
