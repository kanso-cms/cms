<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\ecommerce;

use kanso\cms\ecommerce\UtilityBase;

/**
 * Reviews utility class
 *
 * @author Joe J. Howard
 */
class Reviews extends UtilityBase
{
	/**
     * Get product reviews sorted the upvotes (most relevant) from a product review
     *
     * @access public
     * @param  int $productId Product post id
     * @return int
     */
    public function all(int $productId): array
    {
        $reviews = $this->Query->get_comments($productId);

        usort($reviews, function ($a, $b)
        {
            return $this->reviewUpVotes($b->id) - $this->reviewUpVotes($a->id);
        });

        return $reviews;
    }

    /**
     * Get product review rating number
     *
     * @access public
     * @param  int $commentId Comment id
     * @return int
     */
    public function rating(int $commentId): int
    {
        $rating = $this->sql()->SELECT('rating')->FROM('product_reviews')->WHERE('comment_id', '=', $commentId)->ROW();
        
        if ($rating)
        {
            return intval($rating['rating']);
        }
        
        return 0;
    }

    /**
     * Get product review recommended value
     *
     * @access public
     * @param  int $commentId Comment id
     * @return bool
     */
    public function reccomends(int $commentId): bool
    {
        $recommended = $this->sql()->SELECT('recommended')->FROM('product_reviews')->WHERE('comment_id', '=', $commentId)->ROW();
        
        if ($recommended)
        {
            return boolval($recommended['recommended']);
        }
        
        return false;
    }

    /**
     * Get the upvotes from a product review
     *
     * @access public
     * @param  int $commentId Comment id
     * @return int
     */
    public function upVotes(int $commentId): int
    {
        return count($this->sql()->SELECT('id')->FROM('product_review_votes')->WHERE('comment_id', '=', $commentId)->AND_WHERE('up_vote', '=', true)->FIND_ALL());
    }

    /**
     * Get the downvotes from a product review
     *
     * @access public
     * @param  int $commentId Comment id
     * @return int
     */
    public function downVotes(int $commentId): int
    {
        return count($this->sql()->SELECT('id')->FROM('product_review_votes')->WHERE('comment_id', '=', $commentId)->AND_WHERE('up_vote', '=', false)->FIND_ALL());
    }

    /**
     * Get the downvotes from a product review
     *
     * @access public
     * @param  int $commentId Comment id
     * @return int
     */
    public function upVote(int $commentId): int
    {
        $row = 
        [
            'comment_id' => $commentId,
            'up_vote'    => true,
            'ip_address' => $this->Request->environment()->REMOTE_ADDR,
        ];

        return $this->sql()->INSERT_INTO('product_review_votes')->VALUES($row)->QUERY();
    }

    /**
     * Get the downvotes from a product review
     *
     * @access public
     * @param  int $commentId Comment id
     * @return int
     */
    public function downVote(int $commentId): int
    {
        $row = 
        [
            'comment_id' => $commentId,
            'up_vote'    => false,
            'ip_address' => $this->Request->environment()->REMOTE_ADDR,
        ];

        return $this->sql()->INSERT_INTO('product_review_votes')->VALUES($row)->QUERY();
    }

    /**
     * Get a product's ratings data
     *
     * @access public
     * @param  int $productId Product post id
     * @return array
     */
    function ratings(int $productId): array
    {
        $total   = 0;
        $avg     = 0;
        $star1   = 0;
        $star2   = 0;
        $star3   = 0;
        $star4   = 0;
        $star5   = 0;
        $best    = 0;
        
        $ratings = $this->sql()->SELECT('rating')->FROM('product_reviews')->WHERE('product_id', '=', $productId)->FIND_ALL();
        
        if ($ratings)
        {
            foreach ($ratings as $rating)
            {
                $star   = intval($rating['rating']);
                $total += $star;
                if ($star === 1)
                {
                    $star1 += 1;
                }
                else if ($star === 2)
                {
                    $star2 += 1;
                }
                else if ($star === 3)
                {
                    $star3 += 1;
                }
                else if ($star === 4)
                {
                    $star4 += 1;
                }
                else if ($star === 5)
                {
                    $star5 += 1;
                }
                if ($star > $best)
                {
                    $best = $star;
                }
            }
            $avg = round($total/count($ratings), 1);
        }
        else 
        {
            $avg = 0;
        }
        
        return 
        [
            'average' => $avg,
            'count'   => count($ratings),
            'best'    => $best,
            'stars'   => [
                [
                    'number' => 5,
                    'count'  => $star5,
                ],
                [
                    'number' => 4,
                    'count'  => $star4,
                ],
                [
                    'number' => 3,
                    'count'  => $star3,
                ],
                [
                    'number' => 2,
                    'count'  => $star2,
                ],
                [
                    'number' => 1,
                    'count'  => $star1,
                ]
            ],
        ];
    }
}
