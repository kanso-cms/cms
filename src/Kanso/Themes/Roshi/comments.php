<?php

function buildComment($comments) {

    $HTML = '';

    foreach ($comments as $comment) {

        $children = !empty($comment['children']) ? buildComment($comment['children']) : '';
        
        $HTML .= '
        <div class="comment-comment-wrap" data-comment-id="'.$comment['id'].'">

            <div class="comment-author-wrap">
                <div class="comment-avatar-wrap">
                    <img alt="" src="'.get_avatar($comment['email'], 160, true).'" class="comment-avatar-img" width="160" height="160" />
                </div>
                <p class="comment-author-name">'.$comment['name'].'</p>
            </div>

            <div class="comment-comment-body">
                <div class="comment-comment-content">
                    '.$comment['content'].'
                </div>
            </div>

            <div (:classes_footer)>
                <time (:classes_time) datetime="'.date("c", $comment['date']).'">'.date('F, d, Y', $comment['date']).'</time> • 
                <a class="comment-comment-link" href="'.get_the_permalink().'#comment-'.$comment['id'].'">#</a> • 
                <a class="comment-reply-link" href="#">Reply</a>
            </div>

            <div class="comment-comment-chidren">
                '.$children.'
            </div>

        </div>
        ';

    }

    return $HTML;
}
    
echo buildComment($comments);


?>
    