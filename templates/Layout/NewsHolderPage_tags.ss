<div class="container">
    <div id="tags" class="row">
        <div class="col-xs-12 cloud">
            <div class="tagCloud">
                <% loop allTags %>
                    <a href="{$Top.URLSegment}/tag/$URLSegment" rel="$News.count()">$Title</a>
                <% end_loop %>
            </div>
            <div class="clearfix"></div>
            <div class="news-socialbuttons"
            <a href="https://twitter.com/share" class="twitter-share-button" data-via="$SiteConfig.TwitterAccount"
               data-dnt="true">Tweet</a>
            <br/>

            <div class="fb-like" data-href="$BaseHref{$URLSegment}" data-send="false" data-layout="button_count"
                 data-width="100" data-show-faces="false" data-font="segoe ui"></div>
            <br/>
        </div>
    </div>
</div>
</div>
<div class="clearfix"></div>
