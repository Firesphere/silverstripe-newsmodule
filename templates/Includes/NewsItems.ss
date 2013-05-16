	<section id="latest_work" class="clear">
      <% loop NewsArchive(3) %> <%-- Check code/extensions/NewsExtension.php for configuration --%>
      <article class="one_third $FirstLast"><% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
          <h2><a href="$Link">$Title</a></h2>
	  <h3>$Author</h3>
	<i><% if PublishFrom %>$PublishFrom.Format(d-m-Y)<% else %>$Created.Format(d-m-Y)<% end_if %></i>
	<p>$Content.Summary</p>
	  <% if Tags.Count > 0 %>
	  <br />
	  <div class="small">
		<% loop Tags %>
		<a href="{$Top.URLSegment}/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
		<% end_loop %>
	  </div>
	  <br />
	  <% end_if %>
          <footer class="more"><a href="$Link">Read More &raquo;</a></footer>
        </article>
	      <% end_loop %>

      </section>