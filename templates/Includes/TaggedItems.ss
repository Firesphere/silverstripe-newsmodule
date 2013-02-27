	<section id="latest_work" class="clear">
      <% loop getTags(true) %>
      <article class="one_third $FirstLast"><% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
	      	      <div class="articleContainer">
          <h2><a href="$Link">$Title</a></h2>
	  <h3>$Author</h3>
	<i><% if PublishFrom %>$PublishFrom.Format(d-m-Y)<% else %>$Created.Format(d-m-Y)<% end_if %> by $Author</i>
	<p>$Content.Summary</p>
	  <% if Tags.Count > 0 %>
	  <br />
	  <div class="small">
		<% loop Tags %>
<!--		Note, don't forget this might need to be static!-->
		<a href="$Up.Up.URLSegment/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
		<% end_loop %>
	  </div>
	  <br />
	  <% end_if %>
          <footer class="more"><a href="$Link">Read More &raquo;</a></footer>
		      </div>	  
        </article>
	      <% end_loop %>

      </section>
