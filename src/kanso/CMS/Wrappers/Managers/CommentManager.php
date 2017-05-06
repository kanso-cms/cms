<?php

/**
 * @copyright Joe J. Howard
 * @license   https://github.com/kanso-cms/cms/blob/master/LICENSE
 */

namespace Kanso\CMS\Wrappers\Managers;

use Kanso\CMS\Wrappers\Managers\Manager;
use Kanso\CMS\Wrappers\Comment;
use Kanso\CMS\Email\Email;
use Kanso\CMS\Wrappers\Providers\CommentProvider;
use Kanso\Framework\Database\Query\Builder;
use Kanso\Framework\Security\SPAM\SpamProtector;
use Kanso\Framework\Http\Request\Environment;
use Kanso\Framework\Utility\Markdown;
use Kanso\Framework\Config\Config;
use Kanso\Framework\Utility\Str;

/**
 * Comment manager
 *
 * @author Joe J. Howard
 */
class CommentManager extends Manager
{
    /**
     * Comment marked as SPAM
     *
     * @var int
     */
    const STATUS_SPAM = 100;

    /**
     * Comment marked as pending
     *
     * @var int
     */
    const STATUS_PENDING = 200;

    /**
     * SPAM protector
     *
     * @var \Kanso\Framework\Security\SPAM\SpamProtector
     */
    private $spamProtector;

    /**
     * HTTP request env
     *
     * @var \Kanso\Framework\Http\Request\Environment
     */
    private $environment;

    /**
     * Framework config
     *
     * @var \Kanso\Framework\Config\Config
     */
    private $config;

    /**
     * CMS Email utility
     *
     * @var \Kanso\CMS\Email\Email
     */
    private $email;

    /**
     * Override inherited constructor
     * 
     * @access public
     * @param  \Kanso\Framework\Database\Query\Builder       $SQL           SQL query builder
     * @param  \Kanso\CMS\Wrappers\Providers\CommentProvider $provider      Comment provider
     * @param  \Kanso\Framework\Security\SPAM\SpamProtector  $spamProtector SPAM protector
     * @param  \Kanso\CMS\Email\Email                        $email         CMS Email utility
     * @param  \Kanso\Framework\Config\Config                $config        Framework config
     * @param  \Kanso\Framework\Http\Request\Environment     $environment   HTTP request env

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
     * Creates a new category
     * 
     * @access public
     * @param  string $name Comment name
     * @param  string $slug Comment slug (optional) (default null)
     * @return mixed
     */
    public function create(string $content, string $name, string $email, int $postId, int $parentId = null, bool $validate = true, bool $subscribeThread = true, bool $subscribeReplies = true, bool $sendEmails = true)
    {
        # Validate the post exists
        if (!$this->SQL->SELECT('id')->FROM('posts')->WHERE('id', '=', $postId)->ROW())
        {
            return false;
        }

        # Validate the parent comment exists
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
            'parent'       => $parentId,
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
     * Deletes a comment by id
     * 
     * @access public
     * @param  int    $id Comment id
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
     * Gets a comment by id
     * 
     * @access public
     * @param  int    $id Comment id
     * @return mixed
     */
    public function byId(int $id)
    {
        return $this->provider->byId($id);
    }

    /**
     * Returns a comment rating
     * 
     * @access private
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
            else if ($this->spamProtector->isSpam($content) || $this->spamProtector->isIpBlacklisted($this->environment->REMOTE_ADDR))
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
     * Returns a comment status based on rating
     * 
     * @access private
     * @param  int     $rating Comment rating
     * @return string
     */
    private function commentStatus(int $rating): string
    {
        # Create the status
        if ($rating < 0)
        {
            return 'spam';
        }
        else if ($rating === 0)
        {
            return 'pending';
        }
        
        return 'approved';
    }

    private function sendCommentEmails(Comment $comment)
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
                'the_pemalink'  => $this->environment->HTTP_HOST.'/'.trim($post['slug'], '/').'/',
                'the_title'     => $post['title'],
                'the_excerpt'   => Str::reduce($post['excerpt'], 150, '...'),
                'the_thumbnail' => '',
                'websiteName'   => $this->environment->DOMAIN_NAME,
                'websiteUrl'    => $this->environment->HTTP_HOST,
            ];
            
            $senderName   = $this->config->get('cms.site_title');
            $senderEmail  = 'no-reply@'.$this->environment->DOMAIN_NAME;
            $emailSubject = 'Welcome to '.$this->config->get('cms.site_title');
            $emailContent = $this->email->html($emailSubject, $this->email->preset('comment', $emailData));
            $emailTo      = $email;

            $this->email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);

            $sent[] = $email;
        }
    }

    /**
     * Get all the administrator email addresses
     *
     * @access private
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
     * Get all email addresses that are subscribed to receive emails
     *
     * @access private
     * @param  int     $postId Post id
     * @return array
     */
    private function getCommentThreadEmails(int $postId): array
    {
        $comments = $this->SQL->SELECT('*')->FROM('comments')->WHERE('post_id', '=', $postId)->FIND_ALL();
        
        $emails = [];

        foreach($comments as $comment)
        {
            if ($comment['email_thread'] == true )
            {
                $emails[$comment['email']] = $comment['name'];
            }
        }

        return $emails;
    }

    /**
     * Get all email addresses that are subscribed to receive reply emails
     *
     * @access private
     * @param  int     $postId    Post id
     * @param  int     $commentId The current comment to skip
     * @return array
     */
    private function getCommentReplyEmails(int $parentId, int $commentId): array
    {
        $comments = $this->SQL->SELECT('*')->FROM('comments')->WHERE('parent', '=', $parentId)->FIND_ALL();
        
        $emails = [];

        foreach($comments as $comment)
        {
            if ($comment['email_reply'] == true && $comment['id'] !== $commentId)
            {
               $emails[$comment['email']] = $comment['name'];
            }
        }

        return $emails;
    }

}
