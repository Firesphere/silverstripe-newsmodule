<?php
/**
 * The LatestNews widget to be globally used.
 * 
 * Requires the Widgets module.
 * 
 * @package Silverstripe
 * @subpackage Newsmodule
 * @author Simon 'Sphere' Erkelens
 */
/** Only if the Widget module is installed, add this widget. */
if (class_exists('Widget')) {

	class LatestnewsWidget extends Widget
	{
		private static $db = array(
			'WidgetTitle' => 'Varchar(255)',
			'Amount' => 'Int'
		);
		private static $defaults = array(
			'WidgetTitle' => 'Latest news',
		);
		private static $cmsTitle = 'News widget';
		private static $description = 'Widget showing the latest newsitems';

		public function getCMSFields()
		{
			$fields = FieldList::create();
			$fields->push(TextField::create('WidgetTitle', 'Title of this widget'));
			$fields->push(TextField::create('Amount', 'Amount of items to show'));

			return $fields;
		}

		public function latestNews()
		{
			return Controller::curr()->NewsArchive($this->Amount);
		}

		public function Title()
		{
			return $this->WidgetTitle;
		}

	}
}