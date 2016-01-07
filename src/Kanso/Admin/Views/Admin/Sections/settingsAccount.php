<h3>Administrator Settings</h3>

<?php // <!-- IF USERNAME IS STILL ADMIN PUT A NOTIFICATION --> ?>
<?php 
	$adminUser = adminGetUser();
?>

<?php if ($adminUser['username'] === 'admin') : ?>
	<div class="row">
		<div class="message info">
			<div class="message-icon">
				<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#info"></use></svg>
			</div>
			<div class="message-body">
				<p>Your account is still using the username "Admin". It's strongly advised you change your username and password for sercurity reasons.</p>
			</div>
		</div>
	</div>
<?php endif;?>


<?php // <!-- ACCOUNT SETTINGS FORM --> ?> 
<form class="ajax-form js-update-admin-form">
	
	<p class="info text-masked" style="margin-bottom:25px;">
		Your administrator settings are used to login to your Kanso control panel. Keep your password and username in safe place.
	</p>
	
	<div class="input-wrap">
		<label class="bold">Username</label>
		<input class="input-default small" type="text" name="username" data-js-required="true" data-js-validation="no-spaces-text" data-js-min-legnth="5" data-js-max-legnth="30" maxlength="30" value="<?php echo $adminUser['username']?>" placeholder="John-Appleseed" autocomplete="off"/>
		<p class="input-error">* Your username needs to be at least five characters long, no spaces or special characters.</p>
	</div>

	
	<div class="input-wrap">
		<label class="bold">Email</label>
		<input class="input-default small" type="text" name="email" data-js-validation="email" data-js-max-legnth="100" data-js-required="true" maxlength="100" value="<?php echo $adminUser['email']?>" placeholder="John@Appleseed.com" autocomplete="off"/>
		<p class="input-error">* Please enter a valid email address.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Password</label>
		<input class="input-default small" type="password" name="password" data-js-min-legnth="6" data-js-validation="password" data-js-max-legnth="100" maxlength="100" value="" placeholder="•••••••••" autocomplete="off"/>
		<p class="input-error">* Your password needs to be more than six characters long, including at least one number or special character.</p>
	</div>

	<div class="input-wrap">
		<div class="check-wrap">
			<p class="bold label">Email Notifications</p>
			<p class="info text-masked">
				Do you want to receive email notifications whenever a new comment is made.
			</p>
			<input id="notificationsCheck" type="checkbox" name="email_notifications" <?php if ( (bool) $adminUser['email_notifications'] ) echo 'checked=""'; ?>>
			<label class="checkbox small" for="notificationsCheck"></label>
		</div>
	</div>


	<div class="input-wrap">
		<button type="submit" class="button submit with-spinner">
			Update Settings
			<span class="spinner1"></span>
			<span class="spinner2"></span>
		</button>
	</div>

</form>