<h3><%t NewsHolderPage_show.COMMENTS "Comments" %></h3>
<div id="comments" class="col-xs-12">
	<ul>
	<% loop $getAllowedComments %>
		<li class="comment_$EvenOdd $EvenOdd comment">
			<div class="commenter_name">
				<% if $ShowGravatar %>
					<figure>
						<img src="$Gravatar" width="$Top.SiteConfig.GravatarSize" height="$Top.SiteConfig.GravatarSize" alt="$Name" />
					</figure>
				<% end_if %>
				<address>
					<strong>$Title</strong>
					<%t NewsHolderPage_show.BYWHO "by " %>
					<strong>
						<% if URL %>
							<a href="$URL" target="_blank">$Name</a>
						<% else %>$Name<% end_if %>
					</strong>
				</address>&nbsp;
				<time datetime="$Created">
					<%t NewsHolderPage_show.ONDATE "on" %> $Created.Format(d-m-Y)
				</time>
			</div>
			<section>
				$Comment
			</section>
		</li>
	<% end_loop %>
	</ul>
</div>
<hr>