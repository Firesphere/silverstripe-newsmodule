<?php
/**
 * To clean up and make the News object cleaner.
 * Also, easier reading for functions etc.
 * It has no super powers.
 * 
 * @package News/blog module
 * @author Simon 'Sphere'
 */
class NewsCMSExtension extends DataExtension {
	
	protected $type_array = array();
	
	protected $field_list = array();
	
	/**
	 * Create the fieldlist in the admin
	 * @param FieldList $fields
	 */
	public function updateCMSFields(FieldList $fields) {
		/** @var News $owner */
		$owner = $this->owner;
		/** @var SiteConfig $siteConfig */
		$siteConfig = SiteConfig::current_site_config();
		$this->type_array = array(
			'news' => _t('News.NEWSITEMTYPE', 'Newsitem'),
		);
		/** Setup all fields according. Their visibility is based on the origin, which can be read from the functionname and comments. */
		$this->defaultFields();
		$this->multipleNewsHolderPages();
		$this->siteConfigFields($owner, $siteConfig);
		$this->existingItem($owner, $siteConfig);
		$this->displayLogic();
		$this->createHelptab();
		if(count($this->type_array) > 1){
			$this->field_list['Root.Main'][3] = OptionsetField::create('Type', _t('News.NEWSTYPE', 'Type of item'), $this->type_array, $owner->Type);
		}
		$this->setupFields($owner, $fields);
	}
	
	/**
	 * Setup the default fields that are always available.
	 */
	private function defaultFields() {
		$this->field_list = array(
			'Root.Main' => array(
				0  => TextField::create('Title', _t('News.TITLE', 'Title')),
				6  => HTMLEditorField::create('Content', _t('News.CONTENT', 'Content')),
				8  => TextField::create('Author', _t('News.AUTHOR', 'Author')),
				9  => DateField::create('PublishFrom', _t('News.PUBDATE', 'Publish from'))->setConfig('showcalendar', true),
				10 => CheckboxField::create('Live', _t('News.PUSHLIVE', 'Published')),
				11 => CheckboxField::create('Commenting', _t('News.COMMENTING', 'Allow comments on this item')),
				12 => UploadField::create('Impression', _t('News.IMPRESSION', 'Impression image')),
			)
		);
		$this->field_list['Root.Main'][9]->setConfig('dateformat', 'yyyy-MM-dd');
	}
	
	/**
	 * Create the fields based on the SiteConfig settings.
	 * @param News $owner
	 * @param SiteConfig $siteConfig
	 */
	private function siteConfigFields(News $owner, SiteConfig $siteConfig) {
		/** First the defaults */
		if($siteConfig->UseAbstract){
			$this->field_list['Root.Main'][4] = TextareaField::create('Synopsis', _t('News.SUMMARY', 'Summary/Abstract'));
		}
		if($siteConfig->AllowExternals){
			$this->type_array['external'] = _t('News.EXTERNALTYPE', 'External link');
			$this->field_list['Root.Main'][5] = TextField::create('External', _t('News.EXTERNAL', 'External link'));
		}
		if($siteConfig->AllowDownloads){
			$this->type_array['download'] = _t('News.DOWNLOADTYPE', 'Downloadable file');
			$this->field_list['Root.Main'][7] = UploadField::create('Download', _t('News.DOWNLOAD', 'Downloadable file'));
		}
		/** Setup the tab for comments, if allowed */
		if($siteConfig->Comments){
			$this->field_list['Root'][] =
			Tab::create(
				'Comments',
				_t('News.COMMENTS', 'Comments'),
				GridField::create(
					'Comment', 
					_t('News.COMMENTS', 'Comments'),
					$owner->Comments(), 
					GridFieldConfig_RelationEditor::create()
				)
			);
		}
	}
	
	/**
	 * If there are multiple @link NewsHolderPage available, add the field for multiples.
	 * This includes translation options
	 */
	private function multipleNewsHolderPages() {
		$enabled = false;
		// If we have translations, disable translation filter to get all pages.
		if(class_exists('Translatable')){
			$enabled = Translatable::disable_locale_filter();
		}
		$pages = Versioned::get_by_stage('NewsHolderPage', 'Live');
		// Only add the page-selection if there are multiple. Otherwise handled by onBeforeWrite();
		if(count($pages) > 1){
			$pagelist = array();
			if(class_exists('Translatable')){
				foreach($pages as $page) {
					$pagelist['Root.Main'][$page->ID] = $page->Title . ' ' . $page->Locale;
				}
			}
			else {
				$pagelist = $pages->map('ID', 'Title');
			}
			$this->field_list['Root.Main'][1] = ListboxField::create('NewsHolderPages', _t('News.LINKEDPAGES', 'Linked pages'), $pagelist);
			$this->field_list['Root.Main'][1]->setMultiple(true);
		}
		if($enabled) {
			Translatable::enable_locale_filter();
		}
	}
	
	/**
	 * Setup the fields that are visible ONLY when the item exists already.
	 * @param News $owner
	 * @param SiteConfig $siteConfig
	 */
	private function existingItem(News $owner, SiteConfig $siteConfig) {
		if(!$owner->ID) {
			$this->field_list['Root.Main'][13] = ReadonlyField::create('Tags', _t('News.TAGS', 'Tags'), _t('News.TAGAFTERID', 'Tags can be added after the item has been saved'));
			$owner->Type = 'news';
		}
		else {
			$this->field_list['Root.Main'][13] = CheckboxSetField::create('Tags', _t('News.TAGS', 'Tags'), Tag::get()->map('ID', 'Title'));
			$this->field_list['Root.Main'][2] =
				LiteralField::create('Dummy',
					'<div id="Dummy" class="field readonly">
	<label class="left" for="Form_ItemEditForm_Dummy">Link</label>
	<div class="middleColumn">
	<span id="Form_ItemEditForm_Dummy" class="readonly">
		<a href="'.$owner->AbsoluteLink().'" target="_blank">'.$owner->AbsoluteLink().'</a>
	</span>
	</div>
	</div>'
			);
			if($siteConfig->EnableSlideshow){
				/** @var GridFieldConfig_RecordEditor $gridFieldConfig */
				$gridFieldConfig = GridFieldConfig_RecordEditor::create();
				$gridFieldConfig->addComponent(new GridFieldBulkImageUpload());
				$gridFieldConfig->addComponent(new GridFieldSortableRows('SortOrder'));
				$this->field_list['Root'][] = Tab::create(
					'SlideshowImages',
					_t('News.SLIDE', 'Slideshow'),
					GridField::create(
						'SlideshowImage',
						_t('News.IMAGES', 'Slideshow images'),
						$owner->SlideshowImages()
							->sort('SortOrder'), 
						$gridFieldConfig)
				);
			}
		}
	}
	
	/**
	 * If UncleCheese's module Display Logic is available, upgrade the visible fields!
	 */
	private function displayLogic() {
		if(class_exists('DisplayLogicFormField') && count($this->type_array) > 1){
			$this->field_list['Root.Main'][5]->hideUnless('Type')->isEqualTo('external');
			$this->field_list['Root.Main'][6]->hideUnless('Type')->isEqualTo('news');
			$this->field_list['Root.Main'][7]->hideUnless('Type')->isEqualTo('download');
		}
	}
	
	/**
	 * Create the HELP tab. This should be different, same as applies to other private functions that use $fields
	 */
	private function createHelptab() {
		$helpText = "Publish from is auto-filled with a date if it isn't set. Note that setting a publishdate in the future will NOT make this module auto-tweet. Also, to publish from a specific date, the Published-checkbox needs to be checked. It won't go live if it isn't set to true.";
		$this->field_list['Root'][] =
		Tab::create(
			'Help',
			_t('News.HELPTAB', 'Help'),
			ReadonlyField::create('', _t('News.BASEHELPLABEL', 'Help'), _t('News.BASEHELPTEXT', $helpText))
		);
	}

	/**
	 * Setup the actual fieldlists and tabs.
	 * @param News $owner
	 * @param FieldList $fields
	 * @return FieldList
	 */
	private function setupFields(News $owner, FieldList $fields) {
		// Remove all basic fields from parent, so we can setup as we wish.
		$fields->removeByName(array_keys($owner->db()));
		$fields->removeByName(array_keys($owner->has_one()));
		$fields->removeByName(array_keys($owner->has_many()));
		$fields->removeByName(array_keys($owner->many_many()));
		// For some reason, not all fields are removed from the has_one list. So I'll have to add "ID" to the strings.
		foreach($owner->has_one() as $hasOne) {
			$fields->removeByName($hasOne.'ID');
		}
		$fields->removeByName('Commenting');
		/** @var array $fieldset The array of fields that should be visible */
		$fieldset = $this->field_list;
		foreach($fieldset as $key => $fieldlist) {
			ksort($fieldlist);
			$fields->addFieldsToTab($key, $fieldlist);
		}
		return $fields;
	}

}