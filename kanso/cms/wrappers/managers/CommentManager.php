<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace kanso\cms\wrappers\managers;

use kanso\cms\email\Email;
use kanso\cms\wrappers\Comment;
use kanso\cms\wrappers\providers\CommentProvider;
use kanso\framework\config\Config;
use kanso\framework\database\query\Builder;
use kanso\framework\http\request\Environment;
use kanso\framework\security\spam\SpamProtector;
use kanso\framework\utility\Markdown;
use kanso\framework\utility\Str;

/**
 * Comment manager.
 *
 * @author Joe J. Howard
 */
class CommentManager extends Manager
{
    /**
     * Comment marked as SPAM.
     *
     * @var int
     */
    const STATUS_SPAM = 100;

    /**
     * Comment marked as pending.
     *
     * @var int
     */
    const STATUS_PENDING = 200;

    /**
     * SPAM protector.
     *
     * @var \kanso\framework\security\spam\SpamProtector
     */
    private $spamProtector;

    /**
     * HTTP request env.
     *
     * @var \kanso\framework\http\request\Environment
     */
    private $environment;

    /**
     * Framework config.
     *
     * @var \kanso\framework\config\Config
     */
    private $config;

    /**
     * CMS Email utility.
     *
     * @var \kanso\cms\email\Email
     */
    private $email;

    /**
     * Override inherited constructor.
     *
     * @param \kanso\framework\database\query\Builder       $SQL           SQL query builder
     * @param \kanso\cms\wrappers\providers\CommentProvider $provider      Comment provider
     * @param \kanso\framework\security\spam\SpamProtector  $spamProtector SPAM protector
     * @param \kanso\cms\email\Email                        $email         CMS Email utility
     * @param \kanso\framework\config\Config                $config        Framework config
     * @param \kanso\framework\http\request\Environment     $environment   HTTP request env
     */
    public function __construct(Builder $SQL, CommentProvider $provider, SpamProtector $spamProtector, Email $email, Config $config, Environment $environment)
    {
        $this->SQL = $SQL;

        $this->provider = $provider;

        $this->spamProtector = $spamProtector;

        $this->environment = $environment;

        $this->config = $config;

        $this->email = $email;
    }

    /**
     * {@inheritdoc}
     */
    public function provider(): CommentProvider
    {
        return $this->provider;
    }

    /**
     * Creates a new category.
     *
     * @param  string   $content          Comment name
     * @param  string   $name             Name of the commentor
     * @param  string   $email            Email address of the commentor
     * @param  int      $postId           Post id
     * @param  int|null $parentId         Comment parent id (optional) (default null)
     * @param  bool     $validate         Validate the comment through the spam protector (optional) (default true)
     * @param  bool     $subscribeThread  Subscribe email address to thread notifications (optional) (default true)
     * @param  bool     $subscribeReplies Subscribe email address to reply notifications (optional) (default true)
     * @param  bool     $sendEmails       Send emails to all subscribers and admins (optional) (default true)
     * @return mixed
     */
    public function create(string $content, string $name, string $email, int $postId, int $parentId = null, bool $validate = true, bool $subscribeThread = true, bool $subscribeReplies = true, bool $sendEmails = true)
    {
        // Validate the post exists
        if (!$this->SQL->SELECT('id')->FROM('posts')->WHERE('id', '=', $postId)->ROW())
        {
            return false;
        }

        // Validate the parent comment exists
        if ($parentId)
        {
            if (!$this->byId($parentId))
            {
                return false;
            }
        }

        $rating = $this->rateComment($content, $validate);

        $status = $this->commentStatus($rating);

        $content = htmlentities(trim($content));

        $comment = $this->provider->create([
            'post_id'      => $postId,
            'parent'       => !$parentId ? 0 : $parentId,
            'date'         => time(),
            'type'         => !$parentId ? 'comment' : 'reply',
            'status'       => $status,
            'name'         => $name,
            'email'        => $email,
            'content'      => $content,
            'html_content' => Markdown::convert($content),
            'ip_address'   => $this->environment->REMOTE_ADDR,
            'email_reply'  => $subscribeReplies,
            'email_thread' => $subscribeThread,
            'rating'       => $rating,
        ]);

        if ($comment && $sendEmails)
        {
            $this->sendCommentEmails($comment);
        }

        return $comment;
    }

    /**
     * Deletes a comment by id.
     *
     * @param  int  $id Comment id
     * @return bool
     */
    public function delete(int $id): bool
    {
        $comment = $this->byId($id);

        if ($comment)
        {
            return $comment->delete();
        }

        return false;
    }

    /**
     * Gets a comment by id.
     *
     * @param  int   $id Comment id
     * @return mixed
     */
    public function byId(int $id)
    {
        return $this->provider->byId($id);
    }

    /**
     * Returns a comment rating.
     *
     * @param  string $content Comment content
     * @param  bool   $check   Skip the spam protector
     * @return int
     */
    private function rateComment(string $content, bool $check): int
    {
        $rating = 1;

        if ($check)
        {
            if ($this->spamProtector->isIpWhiteListed($this->environment->REMOTE_ADDR))
            {
                $rating = 1;
            }
            elseif ($this->spamProtector->isSpam($content) || $this->spamProtector->isIpBlacklisted($this->environment->REMOTE_ADDR))
            {
                $rating = -10;
            }
            else
            {
                $rating = $this->spamProtector->rating($content);
            }
        }

        return $rating;
    }

    /**
     * Returns a comment status based on rating.
     *
     * @param  int    $rating Comment rating
     * @return string
     */
    private function commentStatus(int $rating): string
    {
        // Create the status
        if ($rating < 0)
        {
            return 'spam';
        }
        elseif ($rating === 0)
        {
            return 'pending';
        }

        return 'approved';
    }

    private function sendCommentEmails(Comment $comment): void
    {
        $sent   = [];
        $emails = $this->adminEmails();
        $emails = array_merge($emails, $this->getCommentThreadEmails($comment->post_id));

        if (!empty($comment->parent))
        {
            $emails = array_merge($emails, $this->getCommentReplyEmails($comment->parent, $comment->id));
        }

        $post = $this->SQL->SELECT('*')->FROM('posts')->WHERE('id', '=', $comment->post_id)->ROW();

        foreach ($emails as $email => $name)
        {
            if (in_array($email, $sent) || $email === $comment->email)
            {
                continue;
            }

            $emailData =
            [
                'name'          => $name,
                'comment_id'    => $comment->id,
                'comment_email' => $email,
                'the_pemalink'  => $this->environment->HTTP_HOST . '/' . trim($post['slug'], '/') . '/',
                'the_title'     => $post['title'],
                'the_excerpt'   => Str::reduce($post['excerpt'], 150, '...'),
                'the_thumbnail' => '',
                'websiteName'   => $this->environment->DOMAIN_NAME,
                'websiteUrl'    => $this->environment->HTTP_HOST,
            ];

            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@' . $this->environment->DOMAIN_NAME;
            $emailSubject = 'New Comment on ' . $this->config->get('cms.site_title');
            $emailContent = $this->email->html($emailSubject, $this->email->preset('comment', $emailData));
            $emailTo      = $email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

            $sent[] = $email;
        }
    }

    /**
     * Get all the administrator email addresses.
     *
     * @return array
     */
    private function adminEmails(): array
    {
        $emails = [];

        $admins = $this->SQL->SELECT('*')->FROM('users')->WHERE('status', '=', 'confirmed')->AND_WHERE('role', '=', 'administrator')->AND_WHERE('email_notifications', '=', true)->FIND_ALL();

        foreach ($admins as $admin)
        {
            $emails[$admin['email']] = $admin['name'];
        }

        return $emails;
    }

    /**
     * Get all email addresses that are subscribed to receive emails.
     *
     * @param  int   $postId Post id
     * @return array
     */
    private function getCommentThreadEmails(int $postId): array
    {
        $comments = $this->SQL->SELECT('*')->FROM('comments')->WHERE('post_id', '=', $postId)->FIND_ALL();

        $emails = [];

        foreach($comments as $comment)
        {
            if ($comment['email_thread'] > 0)
            {
                $emails[$comment['email']] = $comment['name'];
            }
        }

        return $emails;
    }

    /**
     * Get all email addresses that are subscribed to receive reply emails.
     *
     * @param  int   $parentId  Post id
     * @param  int   $commentId The current comment to skip
     * @return array
     */
    private function getCommentReplyEmails(int $parentId, int $commentId): array
    {
        $comments = $this->SQL->SELECT('*')->FROM('comments')->WHERE('parent', '=', $parentId)->FIND_ALL();

        $emails = [];

        foreach($comments as $comment)
        {
            if ($comment['email_reply'] > 0 && $comment['id'] !== $commentId)
            {
               $emails[$comment['email']] = $comment['name'];
            }
        }

        return $emails;
    }

}
