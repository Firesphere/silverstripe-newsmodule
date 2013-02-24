<?php

// make News-functions available to controller and templates:
Object::add_extension('ContentController', 'NewsExtension');
// Setup siteconfig
Object::add_extension('SiteConfig', 'NewsSiteConfigDecorator');
// Use an icon
LeftAndMain::require_css('silverstripe-newsmodule/css/news_icon.css');
// Setup Akismet. Disable when you don't have an Akismet API key.
SSAkismet::setAPIKey('YOURAPIKEY');

/**
 * Remove or comment the following lines to disable features.
 * You can register the YouTube feature to other shortcodes, but I prefer shorter bb-code.
 */
ShortcodeParser::get()->register('tweet',array('NewsHolderPage','TweetHandler'));
ShortcodeParser::get()->register('code',array('NewsHolderPage','GeshiParser'));
ShortcodeParser::get()->register('YT',array('NewsHolderPage','YouTubeHandler'));
