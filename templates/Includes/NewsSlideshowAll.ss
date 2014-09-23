<% loop SlideshowImages %>
<a title="$Description" rel="lightbox.$Up.Title" href="$Image.Link" >$Image.SetSize(200,200)</a>
<% end_loop %>