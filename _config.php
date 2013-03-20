<?php

// make News-functions available to controller and templates:
Object::add_extension('ContentController', 'NewsExtension');
// Setup siteconfig
Object::add_extension('SiteConfig', 'NewsSiteConfigDecorator');
// Use an icon
LeftAndMain::require_css('silverstripe-newsmodule/css/news_icon.css');

/**
 * Remove or comment the following lines to disable features.
 * You can register the YouTube feature to other shortcodes, but I prefer shorter bb-code.
 * 
 * To enable, just add a / directly after the star below this line
 *
ShortcodeParser::get()->register(
	array(
 
		'tweet' => array('NewsHolderPage','TweetHandler'),
		'code' => array('NewsHolderPage','GeshiParser'),
		'YT' => array('NewsHolderPage','YouTubeHandler'),
		'slideshow' => array('NewsHolderPage','createSlideshow')
	)
);
/*This comment is here to save you from WTF HAPPENED!*/