<div class="wrapper row1">
  <section id="shout" class="clear">
<% with $currentNewsItem %>
	    <% if $Impression %>
    <figure>
      <figcaption>
	      <% else %>
	      <div class="noImage">
	      <% end_if %>
        <h1>$Title</h1>
	<h3><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></h3>
	<br />
	<div class="Content" contenteditable="true">
	<% if $Content %>
		$Content
	<% else %>
		$Synopsis
	<% end_if %>
	<% if not $Type == news %>
		<% if $Type == External %>
			<a href="$External" target="_blank">$Title</a>
		<% else_if $Type == Download %>
			<a href="$Download.Link">$Title</a>
		<% end_if %>
	<% end_if %>
	</div>
	  <% if $Tags.Count > 0 %>
	  <br />
	  <div class="small">
		<% loop Tags %>
		<a href="{$Top.URLSegment}/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
		<% end_loop %>
	  </div>
	  <br />
	  <% end_if %>
	<br />
	<a href="https://twitter.com/share" class="twitter-share-button" data-via="{Your Twitter Account}" data-dnt="true">Tweet</a>
	<br />
	<div class="fb-like" data-href="$BaseHref{$Up.URLSegment}/show/$URLSegment" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
	<br />
	<% if AllowComments %>
	<hr />
	<% if Comments %>
	<h3><%t NewsHolderPage_show.COMMENTS "Comments" %></h3>
	<section id="comments">
	<ul>
	<% loop $Comments %>
	<% if $AkismetMarked == 0 %>
	<li class="comment_$EvenOdd">
		<header>
			<figure>
				<% if $ShowGravatar %>
				<img src="$Gravatar" width="$Top.SiteConfig.GravatarSize" height="$Top.SiteConfig.GravatarSize" alt="$Name" />
				<% end_if %>
			</figure>
		<address><strong>$Title</strong><%t NewsHolderPage_show.BYWHO "by" %> <strong><% if URL %><a href="$URL">$Name</a><% else %>$Name<% end_if %></strong></address> <time datetime="$Created"><%t NewsHolderPage_show.ONDATE "on" %> $Created.Format(d-m-Y)</time>
		</header>
		<section>
		$Comment
		<br />
		</section>
	</li>
	<% end_if %>
	<% end_loop %>
	</ul>
	</section>
	<hr>
	<% end_if %>
	$Up.CommentForm
	<% end_if %>
	    <% if $Impression %>
      </figcaption>
	    <% with $Impression %>
      <div>$SetSize(410,440)</div>
      <% end_with %>
    </figure>
	<% else_if $Top.SiteConfig.DefaultImage %>
	</figcaption>
	    <div>$Top.SiteConfig.DefaultImage.SetSize(410,440)</div>
	</figure>
	<% else %>
	    </div>
      <% end_if %>
<% end_with %>
  </section>
</div>
<%-- If you want newsitems below this, include NewsItems.ss --%>
