<?php
/**
 * Take the Form-creation outside of the page. This is a cleaner way of getting everything correct.
 * This split makes sure things are easier to view, read and edit.
 *
 * @package News/blog module
 * @author Simon 'Sphere'
 */
class CommentForm extends Form {
	
	/**
	 * Setup the Commenting-form for posting comments
	 * @param Controller $controller The current controller
	 * @param String $name Name of the field
	 * @param SiteConfig $siteconfig Current active SiteConfig
	 * @param array $params Current URL Parameters
	 */
	public function __construct($controller, $name, $siteconfig, $params) {
		$field = array();
		/** Include the ID of the current item. Otherwise we can't link correctly. */
		$NewsID = Controller::curr()->request->postVar('NewsID');
		if($NewsID == null){
			$newsItem = News::get()->filter(array('URLSegment' => $params['ID']))->first();
			$field[] = HiddenField::create('NewsID', '', $newsItem->ID);
		}
		$field[] = TextField::create('Name', _t($this->class . '.COMMENT.NAME', 'Name'));
		$field[] = TextField::create('Title', _t($this->class . '.COMMENT.TITLE', 'Comment title'));
		$field[] = TextField::create('Email', _t($this->class . '.COMMENT.EMAIL', 'E-mail'));
		$field[] = TextField::create('URL', _t($this->class . '.COMMENT.WEBSITE', 'Website'));
		$field[] = TextareaField::create('Comment', _t($this->class . '.COMMENT.COMMENT', 'Comment'));
		/** Check the Readme.MD for details about extra spam-protection */
		if($siteconfig->ExtraSecurity){
			$field[] = TextField::create('Extra', _t($this->class . '.COMMENT.EXTRA', 'Extra'));
		}
		if($siteconfig->NoscriptSecurity){
			$field[] = LiteralField::create('noscript', '<noscript><input type="hidden" value="1" name="nsas" /></noscript>');
		}
		$fields = FieldList::create(
			$field
		);

		$actions = FieldList::create(
			FormAction::create('CommentStore', 'Send')
		);
		$required_fields = array(
			'Name',
			'Title',
			'Email',
			'Comment'
		); 
		$validator = RequiredFields::create($required_fields);

		parent::__construct($controller, $name, $fields, $actions, $validator);
	}
	
	/**
	 * Store it.
	 * And also check if it's no double-post. Limited to 60 seconds, but it can be differed.
	 * I wonder if this is XSS safe? The saveInto does this for me, right?
	 * @param array $data Posted data as array
	 * @param Form $form FormObject containing the entire Form as an Object.
	 */
	public function CommentStore($data, $form){
		/**
		 * If the "Extra" field is filled, we have a bot.
		 * Also, the nsas (<noscript> Anti Spam) is a bot. Bot's don't use javascript.
		 * Note, a legitimate visitor that has JS disabled, will be unable to post!
		 */
		if(!isset($data['Extra']) || $data['Extra'] == '' || isset($data['nsas'])){
			$data['Comment'] = Convert::raw2sql($data['Comment']);
			$exists = Comment::get()
				->filter(array('Comment:PartialMatch' => $data['Comment']))
				->where('ABS(TIMEDIFF(NOW(), Created)) < 60');
			if(!$exists->count()){
				$comment = Comment::create();
				$form->saveInto($comment);
				$comment->NewsID = $data['NewsID'];
				$comment->write();
			}
		}
		Controller::curr()->redirectBack();
	}
}
