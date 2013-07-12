<?php
/**
 * Decorate Left and Main with the CSS for the NewsAdmin
 * This is cleaner than including via _config.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 * @thanks FrozenFire for telling me this actually works this way.
 */
class NewsLeftAndMainExtension extends Extension {
	
	/**
	 * OVERKILL? Yes, but at least it works most of the time.
	 */
	public function onAfterInit(){
		Requirements::css('silverstripe-newsmodule/css/news_icon.css');
	}

}
