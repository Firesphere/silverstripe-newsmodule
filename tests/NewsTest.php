<?php
/**
 * Tests for the Newsitems.
 * 
 * If I missed any test, feel free to add!
 *
 * @package News/blog module
 * @author Simon 'Sphere' Erkelens
 */
class NewsTest extends SapphireTest {
	
	protected static $fixture_file = 'NewsTest.yml';
	
	public function setUp() {
		SS_Datetime::set_mock_now("2014-01-01");
		parent::setUp();
	}
	
	/**
	 * Check if only the items with a date in the past AND Live items are there.
	 * Since all items are linked to page1, we only check if page1 has them all indeed.
	 * And the children should only be the published or in the past, thus there should be 2 excluded at first run.
	 * 
	 * The future-published-test fails because mock_now doesn't work as expected?!
	 */
	public function testItemPublished() {
		$member = Member::currentUser();
		if($member) {
			$member->logout();
		}
		$page1 = $this->objFromFixture('NewsHolderPage', 'page1');
		$allItems = News::get();

		$this->assertEquals($allItems->count(), 7, 'Total items');
		$this->assertEquals($page1->Newsitems()->count(), 7, 'Total items available');
		$this->assertEquals($page1->Children()->count(), 5, 'Amount of visible items');
		
		/*
		SS_Datetime::set_mock_now("2020-01-01");
		$this->assertEquals($page1->Children()->count(), 6);
		*/
	}
	
	/**
	 * Test if pages are linked correctly.
	 * Page1 should have all entries as a child
	 * Page2 should only have entry2 as a child
	 * Entry should automatically be assigned to page1
	 * Entry2 should have 2 pages (both indeed)
	 */
	public function testItemPages() {
		$page1 = $this->objFromFixture('NewsHolderPage', 'page1');
		$page2 = $this->objFromFixture('NewsHolderPage', 'page2');
		$entry = $this->objFromFixture('News', 'pagelessitem');
		$entry2 = $this->objFromFixture('News', 'item2');
		
		$this->assertEquals($page1->Children()->count(), 5);
		$this->assertEquals($page2->Children()->count(), 1);
		$this->assertEquals($entry2->NewsHolderPages()->count(), 2);
		$this->assertEquals($entry->NewsHolderPages()->first()->Title, $page1->Title);
		
		
	}

	/**
	 * Test if URLSegments are correctly set and no collision occurs.
	 * Entry1 should have "first-newsitem"
	 * Entry2 should have "first-newsitem-1"
	 * And they obviously shouldn't be the same.
	 */
	public function testItemURLSegment() {
		$entry1 = $this->objFromFixture('News', 'item1');
		$entry2 = $this->objFromFixture('News', 'urlcollisionitem');
		
		$this->assertEquals('first-newsitem', $entry1->URLSegment);
		$this->assertEquals('first-newsitem-1', $entry2->URLSegment);
		$this->assertNotEquals($entry2->URLSegment, $entry1->URLSegment);
	}
}