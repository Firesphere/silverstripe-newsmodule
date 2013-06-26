<?php
/**
 * Add shortcode features.
 *
 * @author Simon 'Sphere' Erkelens
 */
class ExtraShortcodeParser {
	
	/**
	 * The following three functions are global once enabled!
	 * @param type $arguments from Content.
	 * @return HTML block with the parsed code.
	 */
	public static function TweetHandler($arguments) {
		if(!isset($arguments['id'])){
			return null;
		}
		if(substr($arguments['id'], 0, 4) == 'http'){
			list($unneeded,$id) = explode('/status/', $arguments['id']);
		}
		else{
			$id = $arguments['id'];
		}
		$data = json_decode(file_get_contents('https://api.twitter.com/1/statuses/oembed.json?id='.$id.'&omit_script=true&lang=en'), 1);
		return ($data['html']);
	}
	
	/**
	 * @param string $arguments array with the type
	 * @param type $code string of the code to parse 
	 * @return type HTMLString of parsed code.
	 */
	public static function GeshiParser($arguments, $code){
		if(!isset($arguments['type'])){
			/** Assuming most code is PHP. Feel free to update. Should this be a configurable? */
			$arguments['type'] = 'php';
		}
		$geshi = new GeSHi(html_entity_decode(str_replace('<br>', "\n", $code)), $arguments['type']);
		$geshi->enable_line_numbers(GESHI_NORMAL_LINE_NUMBERS);
		return $geshi->parse_code();
	}
	
	/**
	 * @param type $arguments array of arguments from the content
	 * @param type $caption text between the [] [/] brackets
	 * @return type HTMLString of parsed youtube movie.
	 */
	public static function YouTubeHandler($arguments,$caption = null) {
		// If there's no ID, just stop.
		if (empty($arguments['id'])) {
			return;
		}
		/*** SET DEFAULTS ***/
		$defaults = array(
			'YouTubeID' => $arguments['id'],
			'autoplay' => false,
			'caption' => $caption ? Convert::raw2xml($caption) : false,
			'width' => 640,
			'height' => 385,
		);

		//overide the defaults with the arguments supplied
		$customise = array_merge($defaults,$arguments);
		$template = new SSViewer('YouTube');
		return $template->process(new ArrayData($customise));
	}

	/**
	 * Only works on a functional newsrecord!
	 * This one isn't global, only works if controller is a NHP :D
	 * @param type $arguments null
	 * @return type HTML Parsed for template.
	 */
	public static function createSlideshow($arguments){
		if( Controller::curr() instanceof NewsHolderPage_Controller && ($record = Controller::curr()->getNews())) {
			$SiteConfig = SiteConfig::current_site_config();
			if($SiteConfig->SlideshowInitial){
				$template = 'NewsSlideShowFirst';
			}
			else{
				$template = 'NewsSlideShowAll';
			}
			$record->Image = $record->SlideshowImages()->sort('SortOrder ASC');
			$template = new SSViewer($template);
			return($template->process($record));
		}
	}

}
