<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace app\models;

use kanso\framework\mvc\model\Model;
use kanso\framework\utility\Str;

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
    	// $_POST
    	$post = $this->Request->fetch();

    	// GUMP
    	$validation = $this->Validation;

    	// Sanitize and validate the POST variables
        $post = $validation->sanitize($post);

        $validation->validation_rules([
            'name'         => 'required',
            'email'        => 'required|valid_email',
            'content'      => 'required',
            'email-reply'  => 'required|boolean',
            'email-thread' => 'required|boolean',
            'post-id'      => 'required|integer',
        ]);

        $validation->filter_rules([
            'name'         => 'trim|sanitize_string',
            'email'        => 'trim|sanitize_email',
            'content'      => 'trim|sanitize_string',
            'post-id'      => 'sanitize_numbers',
            'reply-id'     => 'sanitize_numbers',
        ]);

        $validated_data = $validation->run($post);

        if ($validated_data)
        {
            // Extra sanitization
            $row = $validated_data;
            $row['email-thread'] = Str::bool($row['email-thread']);
            $row['email-reply']  = Str::bool($row['email-reply']);
            $row['post-id']      = intval($row['post-id']);
            $row['reply-id']     = intval($row['reply-id']) == 0 ? null : intval($row['reply-id']);

            $comment = $this->CommentManager->create($row['content'], $row['name'], $row['email'], $row['post-id'], $row['reply-id'], true, $row['email-thread'], $row['email-reply']);

            return $comment->status;
        }

        return false;
    }
}
