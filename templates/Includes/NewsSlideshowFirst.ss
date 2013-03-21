<% loop SlideshowImages %>
<% if First %>
	<a title="$Description" rel="lightbox.$Up.Title" href="$Image.Link" >$Image.SetSize(200,200)</a>
<% else %>
<div class="hidden">
	<a title="$Description" rel="lightbox.$Up.Title" href="$Image.Link" >$Image</a>
</div>
<% end_if %>
<% end_loop %>