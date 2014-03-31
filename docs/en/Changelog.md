# Changelog included since version 4.0
=======================

## Version 4.0

* Rewrote the entire codebase to be cleaner.
* Further splitted the getCMSFields into cleaner, more usable functions.
* Minor improvements on security settings.
* Minor bugfixes on set/get methods.
* Cleaned up the handle and allowed functions.
* Set permissions via permission provider.
* Removed NOW() as an SQL-statement. (appearantly, due to escaping, it doesn't work)
* Added the docs.
* Show the date based on the locale
* Templates based on Bootstrap 3
* Cleaned up the templates mess.
* Fixed the Author-pages


## Version 3.3

* Remodeled the relationships.
* Lowered the amount of queries needed for each model.
* Added the AuthorHelper linking. (Author is not yet functional in the frontend)
* Switched to protected set/get methods.
* Cleaned up a lot of code for better readability.
* Moved the getCMSFields to separate classes.
* Improved rewrites/functions to better use the Framework instead of handling thing manually.
* Included naming of the URL Actions.
* Cleaned up some code.

## Version <= 3.2

* Cleaned up a lot of code.
* Added translations.
* Fixed translations.
* Added Transifex.
.
. A lot happened here, between 1.0 and 3.x
.
* Initial release.

## Features <= 3.x

* Handle news and impressions besides the news.
* History of URL-segments.
* Support for custom comments.
* Preferred anti-spam method is NOT using a Captcha.
* Akismet support. Akismet class included in thirdparty dir. Remove it if you have it included already.
* Option to add an "Extra" titled field. If this field is not empty, the post is considered spam. In your CSS, position it absolute and very, very, very much outside the screen.
* Option to add a noscript-field. For the same reason as the Extra, but vice versa, it shouldn't be in the post, if it is, it's spam.
* Globally available NewsArchive function, read the comments in the NewsExtension class for more.
* Geshi code-rendering support. use `[code type=php]your code here[/code]`
* Tweet rendering by Twitter. Include the twitter widget JS and in your content, just do [tweet id=tweetID]
* Configurable from the SiteConfig.
* GridField overview, less clutter in the SiteTree.
* RSS Feed, can be found under `http://yoursite.com/yournewspage/rss` (Note, it seems links in content are not parsed correctly by some versions of the HTTP class in the RSS-entry builder!)
* Tagging of items, shown grouped under `http://yoursite.com/yournewspage/tags` or a specific tag: `http://yoursite.com/tag/tag-urlsegment` where tag-urlsegment is the urlsegment of the tag, ofcourse.
* Selectively disable comments on items, useful for when a commenting-war commences. Just disable for that specific item and you're set.
* Show random items below a post. Or not. Or related items. Or both. Or none. Have it your way!
* Set the publish-date. If, for example, you want to post after a three-date event, but update your item during the event. Note, you still have to check the Publish-checkbox and it won't auto-tweet (yet).
* The publish-date is the date on the frontpage.
* Slideshow option. Just add pictures and use [slideshow] in your $Content
* Posts per Page and Pagination. If set to 0, it won't paginate.
* Reports on comments, how much of them are marked by Akismet and how many are hidden.
* Report on the tag usage.
* Address the items that belong to a page with <% loop Children %> (don't ask, too little too late)
* Archive. Globally addressable from the template via <% loop getArchiveList %>. See included template examples.