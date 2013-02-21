<?php
/**
 * Make the news globally available. So you don't have to be on a NewsHolderPage.
 * Same goes for tags. For if you want a tagcloud in your sidebar, for example.
 * 
 * @package News/blog module
 * @author Simon 'Sphere' 
 */
class NewsExtension extends DataExtension {

	/**
	 * Get all, or a limited, set of items.
	 * @param $limit integer with chosen limit. Called from template via <% loop NewsArchive(5) %> for the 5 latest items.
	 * @todo fix an admin-like feature. If the user has the correct permissions, show all posts, not only live ones.
	 */
	public function NewsArchive($limit = null) {
		$Params = $this->owner->getURLParams();
		if($Params['Action'] == 'show') {
			$item = News::get()->filter(array('URLSegment' => $Params['ID']))->first()->Tags();
			$item = $item->column('ID');
			$news = News::get()
				->leftJoin('Tag_News', 'Tag_News.NewsID = News.ID')
				->where('Tag_News.TagID IN ('. implode(',',$item).')')
				->sort('RAND()');
		} else {
			$news = News::get()->filter(array('Live' => 1));
		}
		if ($limit) {
			// crap, limit doesn't work like this? :(
			$news->limit($limit);
		} else
		if($news->count() == 0){
			return null;
		}
		return($news);
	}

	/**
	 * Just get al tags.
	 * @return type Datalist of all tags
	 */
	public function allTags() {
		return Tag::get();
	}
	
}
