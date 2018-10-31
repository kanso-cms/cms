<p style="text-align: center;font-size: <?php echo $font_size_h3; ?>;font-weight: 300; color: <?php echo $body_color; ?>;">Welcome</p>
<p>Hi there,</p>
<p>You have been invited to become an author on on <a href="<?php echo $websiteUrl; ?>" style="color: <?php echo $link_color; ?>"><?php echo $websiteName; ?></a>.</p>
<p>To get started, you can login to the Admin Panel at :</p>
<p><a href="<?php echo $loginURL; ?>" style="color: <?php echo $link_color; ?>"><?php echo $loginURL; ?></a></p>
<p>Using the following credenitals:</p>
<p><strong>Username: </strong> <?php echo $username; ?></p>
<p><strong>Password: </strong> <?php echo $password; ?></p>
<table>
<tr>
	<td>
    	<p><?php echo \kanso\Kanso::instance()->Email->button($loginURL, 'Login'); ?></p>
	</td>
</tr>
</table>