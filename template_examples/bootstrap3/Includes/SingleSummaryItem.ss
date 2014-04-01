<% if $Impression %>
	<div class="col-xs-2">
		<a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a>
	</div>
<% else_if $Top.SiteConfig.DefaultImage %>
	<div class="col-xs-2">
		<a href="$Link" class="impressionLink col-xs-2">$Top.SiteConfig.DefaultImage.SetSize(50,50)</a>
	</div>
<% end_if %>
<% if $Type == external && Top.SiteConfig.ReturnExternal %>
	<h2><a href='$External' target="_blank">$Title</a></h2>
<% else_if $Type == download && Top.SiteConfig.ReturnExternal %>
	<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
<% else %>
	<h2><a href="$Link">$Title</a></h2>
<% end_if %>
<h3><%t NewsHolderPage.DATEPUBLISH "{date} by " date=$Published %><a href='$AuthorHelper.Link' title='$Author'>$Author</a></h3>
<% if $Synopsis %>
      <p>$Synopsis</p>
<% else %>
      <p>$Content.Summary</p>
<% end_if %>
<div class="more"><a href="$Link"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a></div>
<% if $Tags.Count > 0 %>
<div class="col-xs-12">
	<% loop Tags %>
		<a href="$Link">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
	<% end_loop %>
</div>
<% end_if %>
