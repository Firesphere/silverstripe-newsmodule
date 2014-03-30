<div class="row">
	<section id="newsitem" class="row col-xs-12">
	<% with $News %>
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
		<h1>$Title</h1>
		<!--Author should be upgraded to a link in the future-->
		<h3><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></h3>
		<br />
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
		<% else %>
			$Content
		<% end_if %>
		</div>
		<% if $Tags.Count > 0 %>
			<br />
			<div class="col-xs-12">
				<% loop Tags %>
					<a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
				<% end_loop %>
			</div>
			<br />
		<% end_if %>
		<!--Example sharing buttons!-->
		<div class="col-xs-12 newsitem-socialbuttons">
			<br />
			<a href="https://twitter.com/share" class="twitter-share-button" data-via="{Your Twitter Account}" data-dnt="true">Tweet</a>
			<br />
			<div class="fb-like" data-href="$BaseHref{$Up.URLSegment}/show/$URLSegment" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
			<br />
		</div>
		<% if $AllowComments %>
			<hr />
			<% if $getComments %>
				<h3><%t NewsHolderPage_show.COMMENTS "Comments" %></h3>
				<section id="comments" class="col-xs-12">
					<ul>
					<% loop $getComments %>
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
				</section>
				<hr>
			<% end_if %>
			$Up.CommentForm
		<% end_if %>
		</div>
	<% end_with %>
	</section>
</div>
<%-- If you want newsitems below this, include NewsItems.ss --%>
