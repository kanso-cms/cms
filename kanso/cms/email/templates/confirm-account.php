<p style="text-align: center;font-size: <?php echo $font_size_h3; ?>;font-weight: 300;color: <?php echo $body_color; ?>;">You're Almost There</p>
<p>Hi <?php echo $name; ?>,</p>
<p>Welcome to <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a>. Before we can get started, we need to quickly verify your email address.</p>
<p>You can verify your email address and get started by following the link below:</p>
<p><a href="<?php echo $confirmURL; ?>" style="color: <?php echo $link_color; ?>"><?php echo $confirmURL; ?></a></p>
<table> 
	<tr>
		<td>
	    	<p><?php echo \kanso\Kanso::instance()->Email->button($confirmURL, 'Verify Your Email'); ?></p>
		</td>
	</tr>
</table>
<p>Thank you for choosing <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a>.</p>