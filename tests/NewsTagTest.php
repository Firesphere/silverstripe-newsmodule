<?php
/**
 * Unittests for tags.
 * Tags should be linked to multiple items, but not all, and vice-versa.
 *
 * @package News/blog module
 * @author Simon 'Sphere' Erkelens
 */
class NewsTagTest extends SapphireTest
{
	protected static $fixture_file = 'NewsTest.yml';

	/**
	 * Check if tags are linked correctly.
	 * We expect tag1 to be linked to 6 items.
	 * We expect tag2 to be linked to 4 items.
	 * We expect item1 to have 1 tag.
	 * We expect item2 to have 2 tags.
	 * We expect item3 to have 0 tags.
	 */
	public function testTag()
	{
		$tag1 = $this->objFromFixture('Tag', 'tag1');
		$tag2 = $this->objFromFixture('Tag', 'tag2');
		$entry1 = $this->objFromFixture('News', 'item1');
		$entry2 = $this->objFromFixture('News', 'item2');
		$entry3 = $this->objFromFixture('News', 'item3');

		$this->assertEquals($tag1->News()->count(), 6, 'Tag 1 to items');
		$this->assertEquals($tag2->News()->count(), 4, 'Tag 2 to items');
		$this->assertEquals($entry1->Tags()->count(), 1, 'Tags on item 1');
		$this->assertEquals($entry2->Tags()->count(), 2, 'Tags on item 2');
		$this->assertEquals($entry3->Tags()->count(), 0, 'Tags on item 0');
	}

	/**
	 * See if the tags get the correct URLSegment.
	 * Tests are not deep, because the function used is the same as the News function.
	 * In the news-tests, this URLSegment-generation is tested more thoroughly, if that one returns green, it's pretty sure to say Tags will work fine too.
	 */
	public function testTagURLSegment()
	{
		$tag1 = $this->objFromFixture('Tag', 'tag1');
		$tag2 = $this->objFromFixture('Tag', 'tag2');

		$this->assertEquals('testtag-1', $tag1->URLSegment);
		$this->assertEquals('testtag-2', $tag2->URLSegment);
	}

}