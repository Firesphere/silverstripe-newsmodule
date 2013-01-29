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
	      <div class="clear"></div>
<div class="advert">
	<script type="text/javascript"><!--
google_ad_client = "ca-pub-8070811811760534";
/* New Site bar */
google_ad_slot = "8445263458";
google_ad_width = 468;
google_ad_height = 15;
//-->
</script>
<script type="text/javascript"
src="http://pagead2.googlesyndication.com/pagead/show_ads.js">
</script>
</div>   
<section id="intro" class="clear">

      <% include StaffMembers %>
      <% include Specials %>
</section>
    </div>
        <div class="clear"></div>
  </div>
</div>