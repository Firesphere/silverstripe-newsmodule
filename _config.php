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
 * 
// It seems an array bugs out :(
ShortcodeParser::get()->register('tweet',array('NewsHolderPage','TweetHandler'));
ShortcodeParser::get()->register('code',array('NewsHolderPage','GeshiParser'));
ShortcodeParser::get()->register('YT',array('NewsHolderPage','YouTubeHandler'));
ShortcodeParser::get()->register('slideshow', array('NewsHolderPage','createSlideshow'));
/*This comment is here to save you from WTF HAPPENED!*/