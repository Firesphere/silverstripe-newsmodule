<?php
/**
 * Tagging for your news, so you can categorize everything and optionally even create a tagcloud.
 * In the Holderpage, there's an option for the tags to view everything by tag.
 *
 * @author Simon 'Sphere' Erkelens
 * @package News/Blog module
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
	);
	
	public static $has_one = array(
		'Impression' => 'Image',
	);
	
	public static $many_many = array(
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
		if (_t($this->class . '.SINGULARNAME')) {
			return _t($this->class . '.SINGULARNAME');
		} else {
			return parent::plural_name();
		}   
	}
	
	public function getCMSFields() {
		$fields = FieldList::create(TabSet::create('Root'));

		$fields->addFieldsToTab('Root.Main', 
			array(
				$text = TextField::create('Title', _t($this->class . '.TITLE', 'Title')),
				$html = HTMLEditorField::create('Description', _t($this->class . '.DESCRIPTION', 'Content')),
				$uplo = UploadField::create('Impression', _t($this->class . '.IMPRESSION', 'Impression')),
			)
		);
		return($fields);
	}
	
	/**
	 * This is a funny one... why did I do this again?
	 * Anyway, setup URLSegment. Note, IT DOES NOT CHECK FOR DOUBLES! WHY NOT?!
	 * I don't know actually... I think I forgot :(
	 * The holder-page ID should be set if translatable, otherwise, we just select the first available one. 
	 */
	public function onBeforeWrite(){
		parent::onBeforeWrite();
		if (!$this->URLSegment || ($this->isChanged('Title') && !$this->isChanged('URLSegment'))){
			if($this->ID > 0){
				$Renamed = new Renamed();
				$Renamed->OldLink = $this->URLSegment;
				$Renamed->NewsID = $this->ID;
				$Renamed->write();
			}
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
