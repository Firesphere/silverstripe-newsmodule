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
	 * Test if URLSegments are correctly set and no collision occurs.
	 * Entry1 should have "first-newsitem"
	 * Entry2 should have "first-newsitem-1"
	 * And they obviously shouldn't be the same.
	 */
	public function testItemURLSegment() {
		$entry1 = $this->objFromFixture('News', 'newspost1');
		$entry2 = $this->objFromFixture('News', 'urlcollisionitem');
		$entry1->write();
		$entry2->write();
		
		$this->assertEquals('first-newsitem', $entry1->URLSegment);
		$this->assertEquals('first-newsitem-1', $entry2->URLSegment);
		$this->assertNotEquals($entry2->URLSegment, $entry1->URLSegment);
	}
}