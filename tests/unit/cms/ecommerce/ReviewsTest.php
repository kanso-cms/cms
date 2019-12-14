<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\tests\unit\cms\ecommerce;

use kanso\tests\TestCase;
use Mockery;

/**
 * @group unit
 * @group cms
 */
class ReviewsTest extends TestCase
{
	/**
	 * 
	 */
	public function testAllHas(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(2)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(2)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 2)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', true)->times(2)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([]);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([['up_vote_2'], ['up_vote_1']]);

		$this->assertEquals($reviews->all(1), $comments);
	}

	/**
	 * 
	 */
	public function testAllEmpty(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn([]);

		$this->assertEquals($reviews->all(1), []);
	}

	/**
	 * 
	 */
	public function testRating(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('rating')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_reviews')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn(['rating' => 3]);

		$this->assertEquals($reviews->rating(1), 3);
	}

	/**
	 * 
	 */
	public function testEmptyRating(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('rating')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_reviews')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertEquals($reviews->rating(1), 0);
	}

	/**
	 * 
	 */
	public function testRecommends(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('recommended')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_reviews')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn(['recommended' => 1]);

		$this->assertTrue($reviews->recommends(1));
	}

	/**
	 * 
	 */
	public function testNotRecommends(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('recommended')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_reviews')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn(['recommended' => 0]);

		$this->assertFalse($reviews->recommends(1));
	}

	/**
	 * 
	 */
	public function testNotRecommendsNotExist(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$query->shouldReceive('get_comments')->andReturn($comments);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('recommended')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_reviews')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('ROW')->times(1)->andReturn([]);

		$this->assertFalse($reviews->recommends(1));
	}

	/**
	 * 
	 */
	public function testUpVotes(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', true)->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([[1], [2]]);

		$this->assertEquals($reviews->upVotes(1), 2);
	}

	/**
	 * 
	 */
	public function testNoUpVotes(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', true)->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([]);

		$this->assertEquals($reviews->upVotes(1), 0);
	}

	/**
	 * 
	 */
	public function testDownVotes(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([[1], [2]]);

		$this->assertEquals($reviews->downVotes(1), 2);
	}

	/**
	 * 
	 */
	public function testNoDownVotes(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([]);

		$this->assertEquals($reviews->downVotes(1), 0);
	}

	/**
	 * 
	 */
	public function testUpVote(): void
	{
		$mocks = $this->getMocks();
		extract($mocks);

		$reviews->shouldReceive('sql')->andReturn($sql);
		$sql->shouldReceive('SELECT')->with('id')->times(1)->andReturn($sql);
		$sql->shouldReceive('FROM')->with('product_review_votes')->times(1)->andReturn($sql);
		$sql->shouldReceive('WHERE')->with('comment_id', '=', 1)->times(1)->andReturn($sql);
		$sql->shouldReceive('AND_WHERE')->with('up_vote', '=', false)->times(1)->andReturn($sql);
		$sql->shouldReceive('FIND_ALL')->times(1)->andReturn([[1], [2]]);

		$row =
        [
            'comment_id' => $commentId,
            'up_vote'    => true,
            'ip_address' => $this->Request->environment()->REMOTE_ADDR,
        ];

        return $this->sql()->INSERT_INTO('product_review_votes')->VALUES($row)->QUERY();
	}

	private function getMocks()
	{
		$reviews    = Mockery::mock('\kanso\cms\ecommerce\Reviews')->makePartial();
		$sql        = Mockery::mock('\kanso\framework\database\query\Builder');
		$query      = Mockery::mock('\kanso\cms\query\Query');
		$post       = Mockery::mock('\kanso\cms\wrappers\Post')->makePartial();
		$comment_1  = Mockery::mock('\kanso\cms\wrappers\Comment');
		$comment_2  = Mockery::mock('\kanso\cms\wrappers\Comment');
		$comments   = [$comment_1, $comment_2];

		$comment_1->id  = 1;
		$comment_2->id  = 2;
		$post->comments = $comments;
		$reviews->Query = $query;
		$reviews->shouldAllowMockingProtectedMethods();
		$query->shouldAllowMockingProtectedMethods();

		return
		[
			'reviews'    => $reviews,
			'sql'        => $sql,
			'query'      => $query,
			'post'       => $post,
			'comments'   => $comments,
		];
	}

	

}