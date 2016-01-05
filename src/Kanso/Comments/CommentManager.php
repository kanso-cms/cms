<?php

namespace Kanso\Comments;

/**
 * Comments
 *
 * This class acts as an abstraction layer and provides helper methods
 * for adding/deleteing and interacting with comments in the database
 *
 */
class CommentManager 
{

    /**
     * @var \Kanso\Kanso
     */
    private static $Kanso;

    /********************************************************************************
    * PUBLIC ACCESS FOR ADDING NEW COMMENTS
    *******************************************************************************/

    /**
     * Validate HTTP request 
     *
     * Note this function is envoked directly from the router for all POST
     * requests to domain.com/comments. It validates that the required
     * fields are set and calls appropriate actions
     *
     * @return mixed
     */
    public static function dispatch()
    {
        
        # Set the Kanso Object instance
        self::$Kanso = \Kanso\Kanso::getInstance();

        # 404 on if this is not an ajax  request
        if (!self::$Kanso->Request->isAjax()) {
        	
        	self::$Kanso->Response->setStatus(404);
        	
        	return false;
        }

        # Add the comment
        $response = self::addComment(self::$Kanso->Request->fetch());

        # If the comment was succesful return a JSON response
        if ($response) {

            self::$Kanso->Response->setBody(json_encode( ['response' => 'processed', 'details' => $response]));
            
            self::$Kanso->Response->setheaders( ['Content-Type' => 'application/json']);

            return true;

        }

        # 404 on error/invalid request
        self::$Kanso->Response->setStatus(404);

        return false;

    }

    /**
     * Add a comment to an article
     *
     * @param  array    $commentData          Associative array of comment data
     * @param  array    $spamValidation       Defaults to true (optional). Should spam validation be used 
     *                                        (e.g Adding a comment from the admin panel)
     * @return bool   
     */
    public static function addComment($commentData, $spamValidation = true)
    {

        # Validate that a kanso instance has been called
        if (is_null(self::$Kanso)) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query builder
        $Query = self::$Kanso->Database()->Builder();

        # Validate the input array
        $commentData = self::validateInputData($commentData);

        # Return false if the input array was invalid
        if (!$commentData) return false;

        # Covert string IDs to int
        $commentData['replyID'] = (int)$commentData['replyID'];
        $commentData['postID']  = (int)$commentData['postID'];

        # Convert boolean values
        $commentData['email-reply']  = $commentData['email-reply'] === 'false'  || ! (bool)$commentData['email-reply']  ? false : true;
        $commentData['email-thread'] = $commentData['email-thread'] === 'false' || ! (bool)$commentData['email-thread'] ? false : true;

        # Convert the content from markdown to HTML
        $Parser      = new \Kanso\Parsedown\ParsedownExtra();
        $htmlContent = $Parser->text($commentData['content']);
        $status      = 'approved';
        $spamRating  = 1;

        # Run the comment through the SPAM validator if needed
        if ($spamValidation) {

            $spamFilter  = new \Kanso\Comments\Spam\SpamProtector($commentData['name'], $commentData['email'], $commentData['content'], $htmlContent);

            # If the user is blacklisted, they can't make comments
            if ($spamFilter->isBlacklistedIP()) return false;

            # If the user is whitelisted, they skip spam validation
            if (!$spamFilter->isWhiteListedIP()) {  

                $isSPAM     = $spamFilter->isSPAM();
                $spamRating = $spamFilter->getSPAMrating();

                if ($isSPAM || $spamRating < 0 ) {
                    $status = 'spam';
                }
                else if ($spamRating === 0) {
                    $status = 'pending';
                }
                else {
                    $status = 'approved';
                }
            }
        }

        # Find the existing article
        $articleRow = $Query->SELECT('*')->FROM('posts')->WHERE('id', '=', (int)$commentData['postID'])->FIND();

        # If the article doesn't exist return false
        if (!$articleRow) return false;

        # Save existing comment id's on article locally
        $existingComments = $Query->SELECT('*')->FROM('comments')->WHERE('post_id', '=', (int)$commentData['postID'])->FIND_ALL();

        # Is this a reply comment ?
        $parentID = isset($commentData['replyID']) && 
                    ($commentData['replyID'] > 0) &&
                    (!empty(self::$Kanso->Query->get_comment($commentData['replyID'])))
                    ? $commentData['replyID'] : null;
        
        $type     = !$parentID ? 'comment' : 'reply';

        # Prep data for entry
        $commentRow = [
            'post_id'      => $commentData['postID'],
            'parent'       => $parentID,
            'date'         => time(),
            'type'         => $type,
            'status'       => $status,
            'name'         => $commentData['name'],
            'email'        => $commentData['email'],
            'content'      => $commentData['content'],
            'html_content' => $htmlContent,
            'ip_address'   => self::$Kanso->Environment['CLIENT_IP_ADDRESS'],
            'email_reply'  => $commentData['email-reply'],
            'email_thread' => $commentData['email-thread'],
            'rating'       => $spamRating,
        ];

        # Validate the parent exists
        $parentRow = $parentID ? $Query->SELECT('*')->FROM('comments')->WHERE('id', '=', (int)$parentID)->FIND() : null;
        
        # You cannot reply to spam, deleted or pending comment
        if ($parentRow && $parentRow['status'] !== 'approved') return false;

        # insert new comment
        $Query->INSERT_INTO('comments')->VALUES($commentRow)->QUERY();

        # Get the comment id
        $id = self::$Kanso->Database->lastInsertId();
        $commentRow['id'] = intval($id);

        if ($commentRow['id'] === 0) return false;

        # Get the id of the new comment and 
        # append/set it to article row in the databse
        self::sendCommentEmails($articleRow, $commentRow);

        return $status;
    }

    /**
     * Change the status of an existing comment
     *
     * @param  int       $commentID    Comment ID to change
     * @param  string    $status       The status to be set
     * @return bool   
     */
    public static function changeCommentStatus($commentID, $status) 
    {
        # Validate that a kanso instance has been called
        if (is_null(self::$Kanso)) self::$Kanso = \Kanso\Kanso::getInstance();

        # Get a new Query builder
        $Query = self::$Kanso->Database()->Builder();

        # Validate the status is allowed
        $statuses = ['approved', 'spam', 'deleted', 'pending'];
        if (!in_array($status, $statuses)) return false;

        # Get the comment row from the database
        $commentRow = $Query->SELECT('*')->FROM('comments')->where('id', '=', (int)$commentID)->FIND();

        # Validate the row exists
        if (!$commentRow) return false;

        # Change and save the status
        $Query->UPDATE('comments')->SET(['status' => $status])->WHERE('id', '=', (int)$commentID)->QUERY();
        
        return true;
    }

    /**
     * Black or whitelist or remove from both an IP address from commenting
     *
     * @param  array    $comment    The comment array from the database
     * @return null
     */
    public static function moderateIPAddress($ipAddress, $blackOrWhiteList) 
    {
        if ($blackOrWhiteList === 'whitelist') {
            \Kanso\Comments\Spam\SpamProtector::appendToDictionary($ipAddress, 'whitelist_ip');
            \Kanso\Comments\Spam\SpamProtector::removeFromDictionary($ipAddress, 'blacklist_ip');
        }
        else if ($blackOrWhiteList === 'blacklist') {
            \Kanso\Comments\Spam\SpamProtector::appendToDictionary($ipAddress, 'blacklist_ip');
            \Kanso\Comments\Spam\SpamProtector::removeFromDictionary($ipAddress, 'whitelist_ip');
        }
        else if ($blackOrWhiteList === 'nolist') {
            \Kanso\Comments\Spam\SpamProtector::removeFromDictionary($ipAddress, 'blacklist_ip');
            \Kanso\Comments\Spam\SpamProtector::removeFromDictionary($ipAddress, 'whitelist_ip');
        }
    }

    /**
     * Validate comment array 
     *
     * Validate a comment array for entry into the 
     * the database meets the required rules
     *
     * @return array|bool
     */
    public static function validateInputData($commentData)
    {

    	# Set A GUMP object for validation
        $GUMP     = new \Kanso\Utility\GUMP();

        # Sanitize the post variables
        $postVars = $GUMP->sanitize($commentData);

        # Validation rules
        $GUMP->validation_rules([
            'name'         => 'required|alpha_space|max_len,100|min_len,1',
            'email'        => 'required|valid_email',
            'content'      => 'required|max_len,2000|min_len,1',
            'postID'       => 'required|integer',
            'email-reply'  => 'required|boolean',
            'replyID'      => 'integer',
            'email-thread' => 'required|boolean',
        ]);

        # Sanitization feilds
        $GUMP->filter_rules([
            'name'         => 'trim|sanitize_string',
            'email'        => 'trim|sanitize_email',
            'content'      => 'trim|sanitize_string',
            'replyID'      => 'sanitize_numbers',
            'postID'       => 'sanitize_numbers',
        ]);

        # Validate the POST data
        return $GUMP->run($postVars);

    }

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/

    /**
     * Send comment emails to subscribers where needed
     *
     * @param  array    $articleRow    Associative array of article data
     * @param  array    $newComment    Associative array of comment data
     * @return bool   
     */
    private static function sendCommentEmails($articleRow, $newComment) 
    {

    	# Is this a reply comment
    	$isReply = $newComment['type'] === 'reply';

        # Get a new Query builder
        $Query = self::$Kanso->Database()->Builder();

        # Get all the comments from the article into a multi-array
        $allComments = self::buildCommentTree(self::$Kanso->Query->get_comments((int)$articleRow['id'], false));

        # Get all the emails that are subscibed to the entire article
        $allEmails = self::getAllCommentEmails($allComments);

    	# Get all the admin email address 
        $adminEmails = $Query->SELECT('email')->FROM('authors')->WHERE('status', '=', 'confirmed')->AND_WHERE('role', '=', 'administrator')->AND_WHERE('email_notifications', '=', true)->FIND_ALL();

        # Get all the emails that are subscribed to the thread
        $threadEmails  = [];
        $parentComment = []; 

        if ($isReply) {
            $threadEmails      = self::getThreadEmails(self::getTopCommentThread($newComment, $allComments));
            $parentComment     = self::$Kanso->Query->get_comment($newComment['parent']);
        }

        # Build an array with comment variables to send email
        $website     = self::$Kanso->Environment['KANSO_WEBSITE_NAME'];
        $commentVars = [
            'name'             => $newComment['name'],
            'id'               => $newComment['id'],
            'date'             => $newComment['date'],
            'articlePermalink' => self::$Kanso->Query->the_permalink($articleRow['id']),
            'articleTitle'     => $articleRow['title'],
            'avatar'           => self::$Kanso->Query->get_avatar($newComment['email'], 20, true),
            'content'          => self::cleanHTMLTags($newComment['html_content']),
            'websiteLink'      => self::$Kanso->Environment['HTTP_HOST'],
            'website'          => $website,
        ];

        # If this is a reply we need the parent comment
        if ($isReply) {
           
            # Append the parent comment to the comment vars array
            $commentVars['parent'] = [
                'name'       => $parentComment['name'],
                'id'         => $parentComment['id'],
                'date'       => $parentComment['date'],
                'avatar'     => self::$Kanso->Query->get_avatar($parentComment['email'], 20, true),
                'content'    => self::cleanHTMLTags($parentComment['html_content']),
            ];
        }

        $msg = $isReply ? \Kanso\Templates\Templater::getTemplate($commentVars, 'EmailReplyComment') : \Kanso\Templates\Templater::getTemplate($commentVars, 'EmailStandAloneComment');

        # Send emails to thread subscribers
        if ( $isReply && !empty($threadEmails) ) {
            
            foreach ($threadEmails as $emailAddress => $name) {

                # Don't send emails to the peson commenting
                if ($emailAddress === $newComment['email']) continue;

                # Don't send emails to admins
                if (\Kanso\Utility\Arr::inMulti($emailAddress, $adminEmails)) continue;

                # Send the email
                \Kanso\Utility\Mailer::sendHTMLEmail(
                    $emailAddress, 
                    $website, 
                    'no-reply@'.$website,
                    'Someone just replied to a comment at you made at '.$website.' on '.$articleRow['title'].'.',
                    $msg
                );
            }

        }

        # Send email to all subscribers
        if (!empty($allEmails)) {
            
            foreach ($allEmails as $emailAddress => $name) {

            	# Don't send emails to the peson commenting
            	if ($emailAddress === $newComment['email']) continue;

            	 # Don't send email twice to people who have subscribed to their own comment
                # as well as the entire article
            	if (isset($threadEmails[$emailAddress])) continue;

               	# Don't send emails to admins
                if (\Kanso\Utility\Arr::inMulti($emailAddress, $adminEmails)) continue;                   
             
                \Kanso\Utility\Mailer::sendHTMLEmail(
                    $emailAddress, 
                    $website, 'no-reply@'.$website, 
                    'A new comment was made at '.$website.' on '.$articleRow['title'], 
                    $msg
                );
        	}
        }

        # Send the email to all the admins on the Kanso blog
        $admins = $Query->SELECT('*')->FROM('authors')->WHERE('status', '=', 'confirmed')->AND_WHERE('role', '=', 'administrator')->FIND_ALL();

        foreach ($admins as $admin) {

        	# Don't send emails to the peson commenting
            if ($admin['email'] === $newComment['email']) continue;

            # Add the admin to the comment variables
            $commentVars['admin'] = $admin;

            # Reset the email message
            $msg = \Kanso\Templates\Templater::getTemplate($commentVars, 'EmailAdminComment');

            # Send the email
            \Kanso\Utility\Mailer::sendHTMLEmail(
                $admin['email'], 
                $website, 'no-reply@'.$website, 
                'A new comment was made at '.$website.' on '.$articleRow['title'], 
                $msg
            );
        }

    }

    /**
     * Style <p> tags for email message
     *
     * @param  string    $HTML
     * @return string
     */
    private static function cleanHTMLTags($HTML)
    {
        return str_replace('<p>', '<p style="margin: 10px 0;width: 100%;display: block;margin-top: 0 !important;margin-bottom: 0 !important;color:#2f3335">', $HTML);
    }

    /**
     * Recursively iterate a comment thread upwards
     * untill the top most comment is found
     *
     * @param  array    $comment    The comment array from the database
     * @return array
     */
    private static function getTopCommentThread($comment, $allComments) 
    {
        if ($comment['parent'] > 0 ) {
        	$parentComment = self::$Kanso->Query->get_comment($comment['parent']);
            if ($parentComment && !empty($parentComment)) return self::getTopCommentThread($parentComment, $allComments);
        }
        else {
            foreach ($allComments as $thread) {
                if ($thread['id'] == $comment['id']) return $thread;
            }
            return false;
        }
        # return the origional comment on fallback
        return $comment;
    }

    /**
     * Recursively iterate a comment thread downwards
     * getting all subscribed emails to the thread
     *
     * @param  array    $comment    The comment array from the database
     * @return array
     */
    private static function getThreadEmails($comment)
    {
    	$emails = [];
        if ($comment['email_reply'] == true) $emails[$comment['email']] = $comment['name'];
        if (!empty($comment['children'])) {
            foreach ($comment['children'] as $child) {
                if ($comment['email_reply'] == true) $emails[$comment['email']] = $comment['name'];
                $emails = array_merge($emails, self::getThreadEmails($child));
            }
        }
        return $emails;
    }

    /**
     * Recursively a flat  array of comments into 
     * nested threads
     *
     * @param  array    $comment    The comment array from the database
     * @return array
     */
    private static function buildCommentTree($comments, $parent_id = 0)
    {
        $branch = [];
    
        foreach ($comments as $i => $comment) {
            if ($comment['parent'] == $parent_id) {
                unset($comments[$i]);
                $comment['children'] = self::buildCommentTree($comments, $comment['id']);
                $branch[] = $comment;
            }
        }
    
        return $branch;
    }

    /**
     * Recursively iterate a multi-array of comments downwards
     * getting all subscribed emails to the article comments
     *
     * @param  array    $comment    The comment array from the database
     * @return array
     */
    private static function getAllCommentEmails($comments)
    {
        $emails = [];

        foreach($comments as $comment)
        {
            if ($comment['email_thread'] == true )  {
                $emails[$comment['email']] = $comment['name'];
            }
            if (!empty($comment['children'])) $emails = array_merge($emails, self::getAllCommentEmails($comment['children']));
        }

        return $emails;
    }

}