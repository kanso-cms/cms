<h3>Author Information</h3>

<p class="info text-masked" style="margin-bottom:25px;">
	Your author Information is used by Kanso for articles and is linked to your administrator account. 
	You don't have to fill it all in - only your name and slug are mandatory. 
</p>

<?php // <!-- AUTHOR AVATAR DROP ZONE--> ?> 
<label class="bold">Avatar</label>
<div class="row js-author-hero-drop author-hero-drop-zone">
	<form>
		<div class="upload-bar js-upload-bar"><span style="width:0%;" class="progress"></span></div>
		<div class="upload-prompt dz-message">
			<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#image"></use></svg>
		</div>
	</form>
	<div class="dz-preview dz-image-preview dz-processing dz-success">
		<div class="dz-details">
			<?php echo $authorImg;?>
		</div>
	</div>
</div>

<?php // <!-- AUTHOR SETTINGS FORM --> ?> 
<form class="ajax-form js-author-settings-form">

	<div class="input-wrap">
		<label class="bold">Name</label>
		<input class="input-default small" type="text" name="name" data-js-required="true" data-js-validation="name" data-js-min-legnth="5" data-js-max-legnth="50" maxlength="50" value="<?php echo $ADMIN_USER_DATA['name']?>" placeholder="John Appleseed" autocomplete="off"/>
		<p class="input-error">* Your name needs to be a real name.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Slug</label>
		<input class="input-default small" type="text" name="slug" data-js-required="true" data-js-validation="no-spaces-text" data-js-min-legnth="5" data-js-max-legnth="50" maxlength="50" value="<?php echo $ADMIN_USER_DATA['slug']?>" placeholder="John-Appleseed" autocomplete="off"/>
		<p class="input-error">* Author slugs should contain only plain characters, dashes and underscores are allowed.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Bio</label>
		<textarea class="input-default small" type="text" name="bio" data-js-max-legnth="500" maxlength="500" value="<?php echo $ADMIN_USER_DATA['description']?>" placeholder="John Appleseed is an awesome web developer" autocomplete="off"/><?php echo $ADMIN_USER_DATA['description'];?></textarea>
	</div>

	<div class="input-wrap">
		<label class="bold">Facebook URL</label>
		<input class="input-default small" type="text" name="facebook" data-js-validation="website" data-js-max-legnth="100" maxlength="100" value="<?php echo $ADMIN_USER_DATA['facebook']?>" placeholder="http://facebook.com/john-appleseed" autocomplete="off"/>
		<p class="input-error">* Enter a valid URL: e.g http://example.com/path.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Twitter URL</label>
		<input class="input-default small" type="text" name="twitter" data-js-validation="website" data-js-max-legnth="100" maxlength="100" value="<?php echo $ADMIN_USER_DATA['twitter']?>" placeholder="http://twitter.com/john-appleseed" autocomplete="off"/>
		<p class="input-error">* Enter a valid URL: e.g http://example.com/path.</p>
	</div>

	<div class="input-wrap">
		<label class="bold">Google+ URL</label>
		<input class="input-default small" type="text" name="google" data-js-validation="website" data-js-max-legnth="100" maxlength="100" value="<?php echo $ADMIN_USER_DATA['gplus']?>" placeholder="http://plus.google.com/john-appleseed" autocomplete="off"/>
		<p class="input-error">* Enter a valid URL: e.g http://example.com/path.</p>
	</div>

	<div class="input-wrap">
		<button type="submit" class="button submit with-spinner">
			Update Settings
			<span class="spinner1"></span>
			<span class="spinner2"></span>
		</button>
	</div>

</form>