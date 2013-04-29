<?php
/**
 * Tagging for your news, so you can categorize everything and optionally even create a tagcloud.
 * In the Holderpage, there's an option for the tags to view everything by tag.
 *
 * @author Simon 'Sphere' Erkelens
 * @package News/Blog module
 * @todo implement translations?
 * @todo Semantics
 */
class Tag extends DataObject {
	
	/**
	 * Not too exciting. Description is optional, Could be useful if you have very cryptic tags ;)
	 * @var type 
	 */
	public static $db = array(
		'Title' => 'Varchar(255)',
		'Description' => 'HTMLText',
		'URLSegment' => 'Varchar(255)',
		'Locale' => 'Varchar(10)', // NOT YET SUPPORTED
	);
	
	public static $has_one = array(
		'Impression' => 'Image',
	);
	
	public static $belongs_many_many = array(
		'News' => 'News',
	);

	/**
	 * Define singular name translatable
	 * @return type Singular name
	 */
	public function singular_name() {
		if (_t($this->class . '.SINGULARNAME')) {
			return _t($this->class . '.SINGULARNAME');
		} else {
			return parent::singular_name();
		} 
	}
	
	/**
	 * Define plural name translatable
	 * @return type Plural name
	 */
	public function plural_name() {
		if (_t($this->class . '.PLURALNAME')) {
			return _t($this->class . '.PLURALNAME');
		} else {
			return parent::plural_name();
		}   
	}
	
	public function getCMSFields() {
		$fields = FieldList::create(TabSet::create('Root'));
		$fields->addFieldsToTab(
			'Root', 
			Tab::create(
				'Main',
				_t($this->class . '.MAIN', 'Main'),
				$text = TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				$html = HTMLEditorField::create('Description', _t($this->class . '.DESCRIPTION', 'Content')),
				$uplo = UploadField::create('Impression', _t($this->class . '.IMPRESSION', 'Impression'))
			)
		);
		return($fields);
	}
	
	/**
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one. 
	 * @todo I still have to fix that translatable, remember? ;)
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if (!$this->URLSegment || ($this->isChanged('Title') && !$this->isChanged('URLSegment'))){
			$this->URLSegment = singleton('SiteTree')->generateURLSegment($this->Title);
			if(strpos($this->URLSegment, 'page-') === false){
				$nr = 1;
				while($this->LookForExistingURLSegment($this->URLSegment)){
					$this->URLSegment .= '-'.$nr++;
				}
			}
		}
	}
	
	/**
	 * test whether the URLSegment exists already on another tag
	 * @return boolean if urlsegment already exists yes or no.
	 */
	public function LookForExistingURLSegment($URLSegment) {
		return(Tag::get()->filter(array("URLSegment" => $URLSegment))->exclude(array("ID" => $this->ID))->count() != 0);
	}

}
