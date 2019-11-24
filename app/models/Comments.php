<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\models;

use kanso\framework\mvc\model\Model;

/**
 * Add new comment model.
 *
 * @author Joe J. Howard
 */
class Comments extends Model
{
    /**
     * Validate the incoming request.
     *
     * @return string|false
     */
    public function validate()
    {
    	$rules =
        [
            'name'         => ['required'],
            'email'        => ['required', 'email'],
            'content'      => ['required'],
            'email-reply'  => ['required'],
            'email-thread' => ['required'],
            'post-id'      => ['required', 'numeric'],
        ];
        $filters =
        [
            'name'         => ['trim', 'string'],
            'email'        => ['trim', 'email'],
            'content'      => ['trim', 'string'],
            'post-id'      => ['trim', 'integer'],
            'reply-id'     => ['trim', 'integer'],
            'email-thread' => ['boolean'],
            'email-reply'  => ['boolean'],
        ];

        $validator = $this->container->Validator->create($this->Request->fetch(), $rules, $filters);

        if (!$validator->isValid())
        {
            return false;
        }

        $post = $validator->filter();

        $comment = $this->CommentManager->create($post['content'], $post['name'], $post['email'], $post['post-id'], $post['reply-id'], true, $post['email-thread'], $post['email-reply']);

        return $comment->status;

    }
}
