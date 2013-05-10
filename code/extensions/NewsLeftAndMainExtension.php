<?php
/**
 * Decorate Left and Main with the CSS for the NewsAdmin
 * This is cleaner than including via _config.
 * 
 * @author Simon 'Sphere' Erkelens
 * @thanks FrozenFire for telling me this actually works this way.
 */
class NewsLeftAndMainExtension extends Extension {
	
	public function onAfterInit(){
		Requirements::css('silverstripe-newsmodule/css/news_icon.css');
	}

}
