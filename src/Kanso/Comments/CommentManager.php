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

    /********************************************************************************
    * PUBLIC ACCESS FOR ADDING NEW COMMENTS
    *******************************************************************************/

    public function __construct()
    {

    }

    /**
     * Validate HTTP request 
     *
     * Note this function is envoked directly from the router for all POST
     * requests to domain.com/comments. It validates that the required
     * fields are set and calls appropriate actions
     *
     * @return mixed
     */
    public function dispatch()
    {
        
       $Response = \Kanso\Kanso::getInstance()->Response;
       $isAjax   = \Kanso\Kanso::getInstance()->Request->isAjax();
       $postVars = \Kanso\Kanso::getInstance()->Request->fetch();

        # Ajax response
        if ($isAjax) {

            # Add the comment
            $result= $this->add($postVars);
        	
            # If the comment was successful return a JSON response
            if ($result) {

                $Response->setBody(json_encode( ['response' => 'processed', 'details' => $result]));
                
                $Response->setheaders( ['Content-Type' => 'application/json']);

                return;
            }
        }

        # 404 on error/invalid request
        \Kanso\Kanso::getInstance()->notFound();
    }

    /**
     * Add a comment to an article
     *
     * @param  array    $commentData          Associative array of comment data
     * @param  array    $spamValidation       Defaults to true (optional). Should spam validation be used 
     *                                        (e.g Adding a comment from the admin panel)
     * @return bool   
     */
    public function add($commentData, $spamValidation = true)
    {
        # Get a new Query builder
        $SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Validate the input array
        $commentData = $this->validateInputData($commentData);

        # Return false if the input array was invalid
        if (!$commentData) return false;

        # Covert string IDs to int
        $commentData['replyID'] = intval($commentData['replyID']);
        $commentData['postID']  = intval($commentData['postID']);

        # Convert boolean values
        $commentData['email-reply']  = \Kanso\Utility\Str::bool($commentData['email-reply']);
        $commentData['email-thread'] = \Kanso\Utility\Str::bool($commentData['email-thread']);

        # Convert the content from markdown to HTML
        $Parser      = new \Kanso\Parsedown\ParsedownExtra();
        $htmlContent = $Parser->text($commentData['content']);
        $status      = 'approved';
        $spamRating  = 1;

        # Run the comment through the SPAM validator if needed
        if ($spamValidation) {

            $spamFilter = new \Kanso\Comments\Spam\SpamProtector($commentData['name'], $commentData['email'], $commentData['content'], $htmlContent);

            # If the user is blacklisted, they can't make comments
            if ($spamFilter->isBlacklistedIP()) return false;

            # If the user is whitelisted, they skip spam validation
            if (!$spamFilter->isWhiteListedIP()) {  

                $isSPAM     = $spamFilter->isSPAM();
                $spamRating = $spamFilter->getRating();

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
        $articleRow = $SQL->SELECT('*')->FROM('posts')->WHERE('id', '=', $commentData['postID'])->ROW();

        # If the article doesn't exist return false
        if (!$articleRow) return false;

        # Is this a reply comment ?
        $parentID  = null;
        $parentRow = [];
        if (isset($commentData['replyID']) && $commentData['replyID'] > 0) {
            $parent = $SQL->SELECT('id')->FROM('comments')->WHERE('id', '=', $commentData['replyID'])->ROW();
            if (!$parent) return false;
            $parentID  = intval($parent['id']);
            $parentRow = $parent;
        }
        $type = !$parentID ? 'comment' : 'reply';

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
            'ip_address'   => \Kanso\Kanso::getInstance()->Environment['CLIENT_IP_ADDRESS'],
            'email_reply'  => $commentData['email-reply'],
            'email_thread' => $commentData['email-thread'],
            'rating'       => $spamRating,
        ];

        # insert new comment
        $SQL->INSERT_INTO('comments')->VALUES($commentRow)->QUERY();

        # Get the comment id
        $id = \Kanso\Kanso::getInstance()->Database->lastInsertId();
        $commentRow['id'] = intval($id);

        if ($commentRow['id'] === 0) return false;

        # Get the id of the new comment and 
        # append/set it to article row in the databse
        $this->sendCommentEmails($articleRow, $commentRow);

        return $status;
    }

    /**
     * Delete a comment
     *
     * @param  int       $commentID    Comment ID to change
     * @return bool   
     */
    public function remove($commentID)
    {
        # Intval
        $commentID = intval($commentID);

        # Get a new Query builder
        $SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Get the comment row from the database
        $commentRow = $SQL->SELECT('*')->FROM('comments')->where('id', '=', $commentID)->ROW();

        # Validate the row exists
        if (!$commentRow) return false;

        $this->deleteThread($commentID);

        return true;
    }

    /**
     * Change the status of an existing comment
     *
     * @param  int       $commentID    Comment ID to change
     * @param  string    $status       The status to be set
     * @return bool   
     */
    public function status($commentID, $status) 
    {
        # Delete
        if ($status === 'deleted')  return $this->remove($commentID);

        # Get a new Query builder
        $SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Validate the status is allowed
        $statuses = ['approved', 'spam', 'pending'];
        if (!in_array($status, $statuses)) return false;

        # Get the comment row from the database
        $commentRow = $SQL->SELECT('*')->FROM('comments')->where('id', '=', (int)$commentID)->FIND();

        # Validate the row exists
        if (!$commentRow) return false;

        # Change and save the status
        $SQL->UPDATE('comments')->SET(['status' => $status])->WHERE('id', '=', (int)$commentID)->QUERY();
        
        return true;
    }

    /**
     * Black or whitelist or remove from both an IP address from commenting
     *
     * @param  array    $comment    The comment array from the database
     * @return null
     */
    public function moderateIp($ipAddress, $blackOrWhiteList) 
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

    /********************************************************************************
    * PRIVATE HELPER METHODS
    *******************************************************************************/
    
    /**
     * Recursively delete comment replies
     *
     * @param  int    $commentId
     * @return NULL
     */
    private function deleteThread($commentId)
    {
        # Get a new Query builder
        $SQL = \Kanso\Kanso::getInstance()->Database()->Builder();

        # Delete the comment
        $SQL->DELETE_FROM('comments')->WHERE('id', '=', $commentId)->QUERY();

        # Find the direct children
        $children = $SQL->SELECT('*')->FROM('comments')->where('parent', '=', $commentId)->FIND_ALL();

        if (!empty($children)) {
            foreach ($children as $child) {
                $this->deleteThread($child['id']);
            }
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
    private function validateInputData($commentData)
    {

        # Set A GUMP object for validation
        $GUMP = new \Kanso\Utility\GUMP();

        # Sanitize the post variables
        $postVars = $GUMP->sanitize($commentData);

        # Validation rules
        $GUMP->validation_rules([
            'name'         => 'required|alpha_space|max_len,100|min_len,1',
            'email'        => 'required|valid_email',
            'content'      => 'required|max_len,2000|min_len,1',
            'postID'       => 'required|integer',
            'email-reply'  => 'required|boolean',
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

    /**
     * Send comment emails to subscribers where needed
     *
     * @param  array    $articleRow    Associative array of article data
     * @param  array    $newComment    Associative array of comment data
     * @return bool   
     */
    private function sendCommentEmails($articleRow, $newComment) 
    {

    	# Emails to send
        $adminEmails  = $this->adminEmails();
        $threadEmails = $this->commentsEmails($articleRow['id']);
        $replyEamil   = [];
        if ($newComment['type'] === 'reply') {
            $parent = \Kanso\Kanso::getInstance()->Query->get_comment($newComment['parent']);
            if ($parent && $parent['email_reply'] == true) {
                $replyEamil[] = $parent['email'];
            }
        }

        $emails     = array_unique(array_merge($adminEmails, $threadEmails, $replyEamil));
        $sentEmails = [];    

        # Email data
        $env       = \Kanso\Kanso::getInstance()->Environment;
        $config    = \Kanso\Kanso::getInstance()->Config;
        $Email     = \Kanso\Kanso::getInstance()->Email;

        $emailData = [
            'name'          => $newComment['name'],
            'comment_id'    => $newComment['id'],
            'the_pemalink'  => \Kanso\Kanso::getInstance()->Query->the_permalink($articleRow['id']),
            'the_title'     => $articleRow['title'],
            'the_excerpt'   => \Kanso\Utility\Str::reduce(\Kanso\Kanso::getInstance()->Query->the_excerpt($articleRow['id']), 150).'...',
            'the_thumbnail' => \Kanso\Kanso::getInstance()->Query->the_post_thumbnail_src($articleRow['id']),
            'comment_email' => $newComment['email'],
            'homepageUrl'   => $env['HTTP_HOST'],
            'websiteName'   => $env['KANSO_WEBSITE_NAME'],
            'websiteUrl'    => $env['HTTP_HOST'],
        ];

        foreach ($emails as $emailTo) {
            
            if (in_array($emailTo, $sentEmails) || $emailTo === $newComment['email']) continue;

            $senderName   = $config['KANSO_SITE_TITLE'];
            $senderEmail  = 'no-reply@'.$env['KANSO_WEBSITE_NAME'];
            $emailSubject = 'A new comment was made at '.$env['KANSO_WEBSITE_NAME'];
            $emailContent = $Email->html($emailSubject, $Email->preset('comment', $emailData));
            $Email->send($emailTo, $senderName, $senderEmail, $emailSubject, $emailContent);
            $sentEmails[] = $emailTo;
        }
    }

    /**
     * Get all the administrator email addresses
     *
     * @return array
     */
    private function adminEmails()
    {
        $result = [];
        $emails = \Kanso\Kanso::getInstance()->Database()->Builder()->SELECT('email')->FROM('users')->WHERE('status', '=', 'confirmed')->AND_WHERE('role', '=', 'administrator')->AND_WHERE('email_notifications', '=', true)->FIND_ALL();
        foreach ($emails as $email) {
            $result[] = $email['email'];
        }
        return $result;
    }

    /**
     * Get all email addresses that are subscribed to receive emails
     *
     * @param  array    $comments    Array of comments
     * @return array
     */
    private function commentsEmails($post_id)
    {
        $comments = \Kanso\Kanso::getInstance()->Query->get_comments($post_id, false);
        $emails   = [];
        foreach($comments as $comment) {
            if ($comment['email_thread'] == true )  {
                $emails[] = $comment['email'];
            }
        }
        return array_unique($emails);
    }

}