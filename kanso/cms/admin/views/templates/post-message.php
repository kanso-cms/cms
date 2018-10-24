<?php if (isset($POST_RESPONSE) && !empty($POST_RESPONSE)) : ?>
<!-- POST RESPONSE MESSAGE -->
<div class="row floor-sm hide-overflow">
	<div class="msg msg-<?php echo $POST_RESPONSE['class']; ?>">
	    <div class="msg-icon">
	        <span class="glyph-icon glyph-icon-<?php echo $POST_RESPONSE['icon']; ?>"></span>
	    </div>
	    <div class="msg-body">
	        <p><?php echo $POST_RESPONSE['msg']; ?></p>
	    </div>
	    <a href="#" class="closer-trigger js-close-msg js-rmv-parent">
	    	<span class="glyph-icon glyph-icon-times"></span>
	    </a>
	</div>
</div>
<?php endif; ?>