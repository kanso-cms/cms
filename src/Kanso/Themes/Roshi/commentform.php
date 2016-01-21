<?php
/**
 * Comment Form template
 *
 * This file will be loaded whenever comment_form() is called
 * and this file exists. If this file does not exists,
 * Kanso's default comment form template will be loaded or 
 * a custom template with the aguements passed to comment_form()
 *
 */
?>
<form class="comment-form">

    <div class="input-wrap comment-form-name">
        <label class="label" for="email">Your name</label>
        <input class="field col-12" id="email" name="name" type="name" placeholder="Name (required)" value="">
        <p class="error-message">* Please enter your name.</p>
    </div>

    <div class="input-wrap comment-form-email">
        <label class="label" for="email">Your email address</label>
        <input class="field col-12" id="email" name="email" type="email" placeholder="Email (required)" value="">
        <p class="error-message">* Please enter a valid email address.</p>
    </div>

     <div class="input-wrap comment-form-comment">
        <label class="label" for="email">Your comment</label>
        <textarea class="field col-12" type="text" name="content" placeholder="Leave a comment..." autocomplete="off"></textarea>
        <p class="error-message">* Please enter a valid comment as plain text.</p>
    </div>

    <div class="input-wrap">
       <div>
            <input type="checkbox" id="email-reply" class="checkbox" name="email-reply"><label for="email-reply"></label>
            <span class="label">Notify me of all comments on this post by email.</span>
        </div>
    </div>

    <div class="input-wrap">
       <div>
            <input type="checkbox" id="email-thread" class="checkbox" name="email-thread"><label for="email-thread"></label>
            <span class="label">Notify me of all comments on this post by email.</span>
        </div>
    </div>

    <input type="hidden" name="postID"  style="display:none" value="<?php echo the_post_id(); ?>">
    <input type="hidden" name="replyID" style="display:none" value="">

    <div class="form-result">

    </div>

    <div class="input-wrap comment-form-submit">
        <button class="button submit" type="submit">Submit Comment</button>
    </div>

</form>