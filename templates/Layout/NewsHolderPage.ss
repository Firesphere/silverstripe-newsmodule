<div class="wrapper row1">
<% include Header %>
<% include LatestNews %>
</div>
<div class="wrapper row2">
  <div id="container">
    <div id="homepage">
      <section id="latest_work">
	      <% cached allNews.max(Created) %>
      <% loop allNews %>
      <article class="one_third $FirstLast <% if IsThird %>First<% end_if %>">
	      	      <div class="articleContainer">
	      <% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
          <h2><a href="$Link">$Title</a></h2>
	  <h3>$Created.Format(d-m-Y) by $Author</h3>
          <p>$Content.Summary</p>
          <footer class="more"><a href="$Link">Read More &raquo;</a></footer>
	</div>
        </article>
	      <% end_loop %>
	      <% end_cached %>
   
      </section>
<section id="intro" class="clear">

      <% include StaffMembers %>
      <% include Specials %>
</section>
    </div>
        <div class="clear"></div>
  </div>
</div>
