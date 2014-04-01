<?php
/**
 * Tests for the Newsitems
 *
 * @author Simon 'Sphere' Erkelens
 */
class NewsTest extends SapphireTest {
	
	protected static $fixture_file = 'NewsTest.yml';
	
	public function setUp() {
		SS_Datetime::set_mock_now("2013-10-10 20:00:00");
		parent::setUp();
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