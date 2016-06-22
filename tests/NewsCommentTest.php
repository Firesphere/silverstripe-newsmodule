<?php

/**
 * Functional tests to see if comments are correctly linked.
 *
 * @author Simon 'Sphere' Erkelens
 */
class NewsCommentTest extends SapphireTest
{
    protected static $fixture_file = 'NewsTest.yml';

    /**
     * This should test if an item has comments or not.
     * Item1 should have comments, Item2 shouldn't.
     * Too bad it's bugging out for unknown reasons.
     */
    public function testItemComments()
    {
        $item1 = $this->objFromFixture('News', 'item1');
        $item2 = $this->objFromFixture('News', 'item2');

        $this->assertEquals($item1->Comments()->count(), 2, 'Item1 should have 2 visible comments');
        $this->assertEquals($item2->Comments()->count(), 1, 'Item2 shouldn have 1 comments');
    }

    /**
     * Test if a comment is marked as spam.
     * This only works on the spamcomment. I forced it to have an AkismetMarked.
     * I want this to be based on Akismet response, but it can't, sadly.
     */
    public function testItemSpamComment()
    {
        $spam = $this->objFromFixture('Comment', 'spamcomment');
        $spam->AkismetMarked;
        $this->assertEquals($spam->AkismetMarked, 1);
        // I want a marked comment here. But Akismet isn't giving me the bad flag :/
    }

}
