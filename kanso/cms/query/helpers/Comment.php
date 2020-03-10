<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\query\helpers;

/**
 * CMS Query comment methods.
 *
 * @author Joe J. Howard
 */
class Comment extends Helper
{
    /**
     * Are comments (if enabled globally) enabled on the current post or a post by id.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return bool
     */
    public function comments_open(int $post_id = null): bool
    {
        if ($this->container->Config->get('cms.enable_comments') === false)
        {
            return false;
        }

        if ($post_id)
        {
            $post = $this->parent->helper('cache')->getPostByID($post_id);

            if ($post)
            {
                return $post->comments_enabled == true;
            }

            return false;
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->comments_enabled == true;
        }

        return false;
    }

    /**
     * Does the current post or a post by id have any comments ?
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return bool
     */
    public function has_comments(int $post_id = null): bool
    {
        return !empty($this->parent->get_comments($post_id));
    }

    /**
     * How many approved comments does the current post or a post by id have.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return int
     */
    public function comments_number(int $post_id = null): int
    {
        return count($this->parent->get_comments($post_id));
    }

    /**
     * Get a single comment row from the databse by id.
     *
     * @param  int                              $comment_id Comment id
     * @return \kanso\cms\wrappers\Comment|null
     */
    public function get_comment(int $comment_id)
    {
        return $this->container->CommentManager->byId($comment_id);
    }

    /**
     * Get all of the current post or a post by id's comments.
     *
     * @param  int|null $post_id Post id or null for current post (optional) (Default NULL)
     * @return array
     */
    public function get_comments(int $post_id = null): array
    {
        if ($post_id)
        {
            $post = $this->parent->helper('cache')->getPostByID($post_id);

            if ($post)
            {
                return $post->comments;
            }

            return [];
        }

        if (!empty($this->parent->post))
        {
            return $this->parent->post->comments;
        }

        return [];
    }

    /**
     * Get the HTML that displays the comments of the current post or a post by id.
     *
     * @param  array|null $args    (optional) (default NULL)
     * @param  int|null   $post_id Post id or null for comments of current post (optional) (Default NULL)
     * @return string
     */
    public function display_comments(array $args = null, int $post_id = null): string
    {
        // If there no comments return empty string
        if ($this->parent->comments_number($post_id) === 0)
        {
            return '';
        }

        // HTML string
        $HTML = '';

        // Save the article row locally
        $post  = !$post_id ? $this->parent->post : $this->parent->helper('cache')->getPostByID($post_id);

        // Fallback incase nothing is present
        if (!$post || empty($post)) return '';

        // Save the article permalink locally
        $permalink = $this->parent->the_permalink($post->id);

        // Default comment format
        $defaultFormat = '
            <div (:classes_wrap) data-comment-id="(:id)" id="comment-(:id)">
                
                <div (:classes_body)>
                    
                    <div (:classes_author_wrap)>
                        <div (:classes_avatar_wrap)>
                            <img alt="" src="(:avatar_src)" (:classes_avatar_img) width="(:avatar_size)" height="(:avatar_size)" />
                        </div>
                        <p (:classes_name)>(:comment_name)</p>
                    </div>

                     <div (:classes_meta)>
                        <a (:classes_link) href="(:permalink)#comment-(:id)">(:link_text)</a>  <time (:classes_time) datetime="(:comment_time_GMT)">(:comment_time_format)</time>  
                    </div>

                    <div (:classes_content)>
                        (:comment_content)
                    </div>

                    <a (:classes_reply) href="#">Reply</a>

                </div>

                <div (:classes_children_wrap)>
                    (:children)
                </div>

            </div>
        ';

        // Default options
        $options = [
            'format'             => null,
            'avatar_size'        => 160,
            'link_text'          => '#',
            'time_format'        => 'F, d, Y',
            'classes'            => [
                    'wrap'          => 'comment',
                    'body'          => 'comment-body',
                    'avatar_wrap'   => 'comment-avatar-wrap',
                    'avatar_img'    => 'comment-avatar-img',
                    'author_wrap'   => 'comment-author-wrap',
                    'name'          => 'comment-author-name',
                    'link'          => 'comment-link',
                    'time'          => 'comment-time',
                    'content'       => 'comment-content',
                    'meta'          => 'comment-meta',
                    'reply'         => 'comment-reply-link',
                    'children_wrap' => 'comment-chidren',
                    'child_wrap'    => 'child-comment',
                    'no_children'   => 'comment-no-children',
                ],
        ];

        // If options were set, overwrite the dafaults
        if ($args && is_array($args)) $options = array_merge($options, $args);

        // Set the default format if not provided
        if (!$options['format']) $options['format'] = $defaultFormat;

        // Get the comments as multi-dimensional array
        $comments = $post->comments;

        // If there was an error retrieving the comments return empty string
        if (empty($comments)) return $HTML;

        // Load from template if it exists
        $formTemplate = $this->parent->theme_directory() . DIRECTORY_SEPARATOR . 'comments.php';

        if (file_exists($formTemplate))
        {
            return $this->parent->include_template('comments', ['comments' => $comments]);
        }

        // Start looping comments
        $HTML = $this->commentToString($comments, $options, $permalink, false);

        return $HTML;
    }

    /**
     * Get the HTML that displays the comment form of the current post or a post by id.
     *
     * @param  array|null $args    (optional) (default NULL)
     * @param  int|null   $post_id Post id or null for comments of current post (optional) (Default NULL)
     * @return string
     */
    public function comment_form(array $args = null, int $post_id = null): string
    {
        // Load from template if it exists
        $formTemplate = $this->parent->theme_directory() . DIRECTORY_SEPARATOR . 'commentform.php';
        if (file_exists($formTemplate)) return $this->parent->include_template('commentform');

        // HTML string
        $HTML = '';

        // Save the article row locally
        $post  = !$post_id ? $this->parent->post : $this->parent->helper('cache')->getPostByID($post_id);

        // Fallback incase nothing is present
        if (!$post || empty($post)) return '';

        // Save the article id locally
        $postID = $post->id;

        // Save the article permalink locally
        $permalink   = $this->parent->the_permalink($postID);

        $options = [

            'form_class' => 'comment-form',

            'legend' => '
                <legend>Leave a comment:</legend>
            ',

            'comment_field' => '
                <label for="comment-content">Your comment</label>
                <textarea id="comment-content" type="text" name="content" placeholder="Leave a comment..." autocomplete="off"></textarea>
            ',

            'name_field' => '
                <label for="comment-name">Name:</label>
                <input id="comment-name" type="text" name="name" placeholder="Name (required)" autocomplete="off" />
            ',

            'email_field' => '
                <label for="comment-email">Email:</label>
                <input id="comment-email" type="email" name="email" placeholder="Email (required)" autocomplete="off" />
            ',

            'email_replies_field' => '
                <input id="comment-email-reply" type="checkbox" name="email-reply" /> Notify me of follow-up comments by email:<br>
            ',

            'email_thread_field'  => '
                <input id="comment-email-thread" type="checkbox" name="email-thread" /> Notify me of all comments on this post by email:<br>
            ',

            'post_id_field'  => '
                <input id="comment-postId" type="hidden" name="post-id" style="display:none" value="(:postID)" />
            ',

            'reply_id' => '',

            'reply_id_field' => '
                <input id="comment-replyId" type="hidden" name="reply-id" style="display:none" value="(:replyID)" />
            ',

            'submit_field'   => '
                <button id="comment-submit" type="submit" value="submit">Submit</button>
            ',
        ];

        // If options were set, overwrite the dafaults
        if ($args && is_array($args)) $options = array_merge($options, $args);

        // Replace POSTID and REPLY ID
        $patterns     = ['/\(:postID\)/', '/\(:replyID\)/'];
        $replacements = [$postID, $options['reply_id']];

        // No replies when comments are disabled
        if (!$this->parent->comments_open($post_id))
        {
            $options['reply_id_field'] = '';
        }

        // Default form format
        return preg_replace($patterns, $replacements, '
           <form class="' . $options['form_class'] . '">
                <fieldset>
                    ' . $options['legend'] . '
                    ' . $options['name_field'] . '
                    ' . $options['email_field'] . '
                    ' . $options['comment_field'] . '
                    ' . $options['email_replies_field'] . '
                    ' . $options['email_thread_field'] . '
                    ' . $options['post_id_field'] . '
                    ' . $options['reply_id_field'] . '
                    ' . $options['submit_field'] . '
                </fieldset>
            </form>
        ');
    }

    /**
     * Retrieve the gravatar 'img' tag or src from an email address or md5 hash.
     *
     * @param  string $email_or_md5 The email address or md5 of the current user (optional)
     * @param  int    $size         Image size in px
     * @param  bool   $srcOnly      Should we return only the img src (rather than the actual HTML tag)
     * @return string
     */
    public function get_gravatar(string $email_or_md5, int $size = 160, bool $srcOnly = false)
    {
        $isMd5 = $this->isValidMd5($email_or_md5);

        $isEmail = !filter_var($email_or_md5, FILTER_VALIDATE_EMAIL) === false;

        $domain = $this->container->Request->isSecure() ? 'https://secure.gravatar.com' : 'http://www.gravatar.com';

        // If there is an error with the emaill or md5 default to fallback
        // force a mystery man
        if (!$isMd5 && !$isEmail)
        {
            if ($srcOnly)
            {
                return $domain . '/avatar/0?s=' . $size . '&d=mm';
            }

            return '<img src="' . $domain . '/avatar/0?s=' . $size . '&d=mm"/>';
        }

        $md5 = '';

        if ($isEmail)
        {
            $md5 = md5(strtolower(trim($email_or_md5)));
        }
        elseif ($isMd5)
        {
            $md5 = $email_or_md5;
        }

        if ($srcOnly)
        {
            return $domain . '/avatar/' . $md5 . '?s=' . $size . '&d=mm';
        }

        return '<img src="' . $domain . '/avatar/' . $md5 . '?s=' . $size . '&d=mm"/>';
    }

    /**
     * Recursively build HTML comments (used internally).
     *
     * @param  array  $comments  Recursive array of comment objects
     * @param  array  $options   Comment display options
     * @param  string $permalink URL to current post being displayed
     * @param  bool   $isChild   Is the current comment a child
     * @return string
     */
    private function commentToString(array $comments, array $options, string $permalink, bool $isChild = false): string
    {
        $HTML = '';

        foreach ($comments as $comment)
        {
            $patterns     = [];
            $replacements = [];

            $commentStr = $options['format'];

            // Replace classnames
            foreach ($options['classes'] as $suffix => $classname)
            {
                $patterns[]     = '/\(:classes_' . $suffix . '\)/';
                $class          = 'class="' . $classname;
                if ($suffix === 'wrap' && $isChild)
                {
                    $class .= ' ' . $options['classes']['child_wrap'];
                }

                if ($suffix === 'children_wrap' && empty($comment->children()))
                {
                    $class .= ' ' . $options['classes']['no_children'];
                }

                $replacements[] = $class . '"';
            }

            // Replace ID
            $patterns[]     = '/\(:id\)/';
            $replacements[] = $comment->id;

            // Replace avatar src
            $patterns[]     = '/\(:avatar_src\)/';
            $replacements[] = $this->parent->get_gravatar($comment->email, $options['avatar_size'], true);

            // Replace avatar size
            $patterns[]     = '/\(:avatar_size\)/';
            $replacements[] =  $options['avatar_size'];

            // Replace comment author name
            $patterns[]     = '/\(:comment_name\)/';
            $replacements[] = $comment->name;

            // Replace Link text
            $patterns[]     = '/\(:link_text\)/';
            $replacements[] = $options['link_text'];

            // Replace time text
            $patterns[]     = '/\(:comment_time_GMT\)/';
            $replacements[] = date('c', $comment->date);

            $patterns[]     = '/\(:comment_time_format\)/';
            $replacements[] = date($options['time_format'], $comment->date);

            // Replace content
            $patterns[]     = '/\(:comment_content\)/';
            $replacements[] = $comment->html_content;

            // Replace permalinks
            $patterns[]     = '/\(:permalink\)/';
            $replacements[] = $permalink;

            $commentStr = preg_replace($patterns, $replacements, $commentStr);

            if (!empty($comment->children()))
            {

                $commentStr = preg_replace('/\(:children\)/', $this->commentToString($comment->children(), $options, $permalink, true), $commentStr);
            }
            else
            {
                $commentStr = preg_replace('/\(:children\)/', '', $commentStr);
            }

            $HTML .= $commentStr;
        }

       return $HTML;

    }

    /**
     * is string a valid md5 hash ?
     *
     * @param  string $md5 md5 hash
     * @return bool
     */
    private function isValidMd5(string $md5 =''): bool
    {
        return preg_match('/^[a-f0-9]{32}$/', $md5);
    }
}
