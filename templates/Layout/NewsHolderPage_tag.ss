<div class="wrapper row1">
  <section id="shout" class="clear">
<% with Tag %>
	    <% if Impression %>
    <figure>
      <figcaption>
	      <% else %>
	      <div class="noImage">
	      <% end_if %>
        <h1><%t NewsHolderPage_tag.TAG "Tag" %>: <a href="{$Top.URLSegment}/tags">$Title</a></h1>
	<br />
        $Description
	<br />
	<a href="{$Top.URLSegment}/tags"><%t NewsHolderPage_tag.ALLTAGS "All tags" %>.</a>
	<br />&nbsp;<br />
	<a href="https://twitter.com/share" class="twitter-share-button" data-via="{YOUR TWITTER ACCOUNT}" data-dnt="true">Tweet</a>
	<br />
	<div class="fb-like" data-href="$BaseHref{$Up.URLSegment}/show/$URLSegment" data-send="false" data-layout="button_count" data-width="100" data-show-faces="false" data-font="segoe ui"></div>
	<br />

	    <% if Impression %>
      </figcaption>
	    <% with Impression %>
      <div>$SetSize(410,440)</div>
<% end_with %>
    </figure>
	    <% else %>
	    </div>
      <% end_if %>
<% end_with %>
  </section>
</div>
<div class="wrapper row2">
  <div id="container">
    <div id="homepage">
	<section id="latest_work" class="clear">
	<% loop Tag.News %>
      <article class="one_third $FirstLast"><% if Impression %><a href="$Link" class="impressionLink"><% with Impression %>$SetSize(50,50)<% end_with %></a><% end_if %>
	      	      <div class="articleContainer">
		<% if Type == external %>
			<h2><a href='$External' target="_blank">$Title</a></h2>
		<% else_if Type == download %>
			<h2><a href='$Download.Link' title='Downloadable file'>$Title (<%t NewsHolderPage.DOWNLOADABLE "Download" %>)</a></h2>
		<% else %>
			<h2><a href="$Link">$Title</a></h2>
		<% end_if %>
	  <h3>$Author</h3>
	<i><%t NewsHolderPage.DATEPUBLISH "{date} by {author}"  date=$Published author=$Author %></i>
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
          <footer class="more"><a href="$Link"><%t NewsHolderPage.READMORE "Read More &raquo;" %></a></footer>
		      </div>	  
        </article>
	<% end_loop %>
	</section>
    </div>
        <div class="clear"></div>
  </div>
</div>