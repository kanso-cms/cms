<form class="comment-form">

    <div class="input-wrap comment-form-name">
        <label>Name:</label>
        <input type="text" name="name" placeholder="Name (required)" autocomplete="off">
        <p class="input-error">* Please enter a valid name.</p>
    </div>

    <div class="input-wrap comment-form-email">
        <label>Email:</label>
        <input type="email" name="email" placeholder="Email (required)" autocomplete="off">
        <p class="input-error">* Please enter a email address.</p>
    </div>

    <div class="input-wrap comment-form-content">
        <label>Comment:</label>
        <textarea type="text" name="content" placeholder="Leave a comment..." autocomplete="off"></textarea>
        <p class="input-error">* Please enter a valid comment as plain text.</p>
    </div>

    <div class="check-wrap clear">
        <input id="email-reply-check" type="checkbox" name="email-reply">
        <label class="checkbox small inline left" for="email-reply-check"></label>
        <p class="inline left">Notify me of follow-up comments by email</p>
    </div>

    <div class="check-wrap clear">
        <input id="email-thread-check" type="checkbox" name="email-thread">
        <label class="checkbox small inline left" for="email-thread-check"></label>
        <p class="inline left">Notify me of all comments on this post by email</p>
    </div>

    <input type="hidden" name="postID" style="display:none" value="1">
    <input type="hidden" name="replyID" style="display:none" value="">

    <div class="form-result">

    </div>

    <div class="input-wrap comment-form-submit">
        <button class="button" type="submit">Submit Comment</button>
    </div>

</form>