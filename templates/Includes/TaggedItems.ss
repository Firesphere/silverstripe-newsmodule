	<section id="latest_work" class="clear">
      <% loop getTags(true) %>
      <article class="one_third $FirstLast"><% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
	      	      <div class="articleContainer">
		<% if Type == external %>
			<h2><a href='$External' target="_blank">$Title</a></h2>
		<% else_if Type == download %>
			<h2><a href='$Download.Link' title='Downloadable file'>$Title (<% _t('DOWNLOADABLE', 'Download') %>)</a></h2>
		<% else %>
			<h2><a href="$Link">$Title</a></h2>
		<% end_if %>
	  <h3>$Author</h3>
	<i><% if PublishFrom %>$PublishFrom.Format(d-m-Y)<% else %>$Created.Format(d-m-Y)<% end_if %></i>
	<% if Synopsis %>
	  <p>$Synopsis</p>
	  <% else %>
          <p>$Content.Summary</p>
	  <% end_if %>
	  <% if Tags.Count > 0 %>
	  <br />
	  <div class="small">
		<% loop Tags %>
<!--		Note, don't forget this might need to be static!-->
		<a href="$Top.URLSegment/tag/$URLSegment">$Title</a><% if Last %><% else %>&nbsp;|&nbsp;<% end_if %>
		<% end_loop %>
	  </div>
	  <br />
	  <% end_if %>
          <footer class="more"><a href="$Link">Read More &raquo;</a></footer>
		      </div>	  
        </article>
	      <% end_loop %>

      </section>
