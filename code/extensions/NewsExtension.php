<?php
/**
 * Make the news globally available. So you don't have to be on a NewsHolderPage.
 * 
 * @package News/blog module
 * @author Simon 'Sphere' 
 */
class NewsExtension extends DataExtension {

	public function NewsArchive($limit = null) {
		if ($limit) {
			$news = News::get()->filter(array('Live' => 1))->limit($limit);
		} else {
			$news = News::get()->filter(array('Live' => 1));
		}
		if($news->count() == 0){
			return null;
		}
		return($news);
	}
}
