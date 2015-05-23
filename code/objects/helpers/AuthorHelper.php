<?php

/**
 * Collect the authors for author-specific pages.
 * NOT needed for users to edit, thus NOT in the admin.
 *
 * @package News/blog module
 * @author Simon 'Sphere' Erkelens
 * @property string OriginalName
 * @property string URLSegment
 * @method News NewsItems() The linked Newsitems to this author
 */
class AuthorHelper extends DataObject
{
	private static $db = array(
		'OriginalName' => 'Varchar(255)',
		'URLSegment'   => 'Varchar(255)',
	);
	private static $has_many = array(
		'NewsItems' => 'News',
	);
	private static $indexes = array(
		'URLSegment' => true,
	);

	public function onBeforeWrite()
	{
		parent::onBeforeWrite();
		$nameParts = explode(' ', $this->OriginalName);
		foreach ($nameParts as $key => $namePart) {
			if ($namePart == '') {
				unset($nameParts[$key]);
			}
		}
		$this->OriginalName = implode(' ', $nameParts);
		if (!$this->URLSegment && !AuthorHelper::get()->filter(array('OriginalName' => $this->OriginalName))) {
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->OriginalName);
		}
	}

	/**
	 * Free guess on what this button does.
	 * @param string $action
	 * @return string Link to this object.
	 */
	public function Link($action = 'author/')
	{
		if ($siteConfigAction = SiteConfig::current_site_config()->AuthorAction) {
			$action = $siteConfigAction . '/';
		}
		if ($Page = NewsHolderPage::get()->first()) {
			return ($Page->Link($action . $this->URLSegment));
		}
		return false;
	}

	/**
	 * This is quite handy, for meta-tags and such.
	 * @param string $action The added URLSegment, the actual function that'll return the news.
	 * @return string Link. To the item. (Yeah, I'm super cereal here)
	 */
	public function AbsoluteLink($action = 'author/')
	{
		if ($Page = $this->Link($action)) {
			return (Director::absoluteURL($Page));
		}
	}

}
