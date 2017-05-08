<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers;

use kanso\cms\wrappers\Wrapper;

/**
 * Comment utility wrapper
 *
 * @author Joe J. Howard
 */
class Comment extends Wrapper
{
    private $children;

    /**
     * {@inheritdoc}
     */
    public function save(): bool
	{
        $saved = false;

        if (isset($this->data['id']) && isset($this->data['post_id']))
        {
            $saved = $this->SQL->UPDATE('comments')->SET($this->data)->WHERE('id', '=', $this->data['id'])->QUERY();
        }
        else
        {
            $saved = $this->SQL->INSERT_INTO('comments')->VALUES($this->data)->QUERY();

            if ($saved)
            {
                $this->data['id'] = intval($this->SQL->connection()->lastInsertId());
            }
        }

        return !$saved ? false : true;
	}

	/**
     * {@inheritdoc}
     */
    public function delete(): bool
	{
        if (isset($this->data['id']) && isset($this->data['post_id']))
        {
            if ($this->deleteCommentChildren($this))
            {
                return $this->SQL->DELETE_FROM('comments')->WHERE('id', '=', $this->data['id'])->QUERY() ? true : false;
            }
        }

        return false;
	}

    /**
     * Creates and returns a nested array of comment children
     *
     * @access public
     * @return array
     */
    public function children(): array
    {
        if (is_null($this->children))
        {
           $this->children = $this->commentChildren($this);
        }

        return $this->children;
    }

    /**
     * Recursively delete comment tree
     *
     * @access private
     * @param  kanso\cms\wrappers\Comment $comment Comment
     * @return bool
     */
    private function deleteCommentChildren(Comment $comment): bool
    {
        foreach ($comment->children() as $child)
        {
            $this->deleteCommentChildren($child);

            $this->SQL->DELETE_FROM('comments')->WHERE('id', '=', $child->id)->QUERY();
        }

        return true;
    }

    /**
     * Recursively build comment tree
     *
     * @access private
     * @param  kanso\cms\wrappers\Comment $comment Comment
     * @return array
     */
    private function commentChildren(Comment $comment): array
    {
        $children = [];

        $rows = $this->SQL->SELECT('id')->FROM('comments')->WHERE('parent', '=', $comment->id)->FIND_ALL();

        foreach ($rows as $row)
        {
            $child = new Comment($this->SQL, $row);

            $child->children = $child->children();

            array_push($children, $child);
        }

        return $children;
    }
}
