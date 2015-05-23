<div class="container">
	<% with Tag %>
        <div id="tag" class="row">
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
                <!--This is using the default, it should take the SiteConfig function into consideration. @todo: Use siteconfig over default.-->
                <h1><%t NewsHolderPage_tag.TAG "Tag" %>: <a href="{$Top.URLSegment}/tags">$Title</a></h1>
                <br/>
				$Description
                <br/>
                <a href="{$Top.URLSegment}/tags"><%t NewsHolderPage_tag.ALLTAGS "All tags" %>.</a>
            </div>
            <div class="col-xs-12 tag-socialbuttons newsitem-socialbuttons">
                <a href="https://twitter.com/share" class="twitter-share-button" data-via="$SiteConfig.TwitterAccount"
                   data-dnt="true">Tweet</a>
                <br/>

                <div class="fb-like" data-href="$BaseHref{$Link}" data-send="false" data-layout="button_count"
                     data-width="100" data-show-faces="false" data-font="segoe ui"></div>
                <br/>
            </div>
        </div>
        <div class="clearfix"></div>
        <div class="row">
            <div id="related_news" class="col-xs-12">
				<% loop activeNews %>
                    <div class="col-xs-4 $FirstLast">
						<% include SingleSummaryItem %>
                    </div>
				<% end_loop %>
            </div>
        </div>
	<% end_with %>
</div>
