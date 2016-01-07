<h3>Add User</h3>
<p class="info text-masked" style="margin-bottom:25px;">
	By default, signing up as administrator to Kanso is barred for security reasons. However you can add other users
	by inviting them here. Kanso will send them an email with a special url to register.
</p>

<?php // <!-- ADMINISTRATOR SETTINGS FORM --> ?> 
<form class="ajax-form js-invite-user-form">
	<div class="row no-roof no-floor">
		
		<div class="col col-8 right-gutter input-wrap">
			<label class="bold">Email</label>
			<input class="input-default small" type="text" name="email" data-js-validation="email" data-js-max-legnth="100" data-js-required="true" maxlength="100" value="" placeholder="John@Appleseed.com" autocomplete="off"/>
			<p class="input-error">* Please enter a valid email address.</p>
		</div>
		
		<div class="col col-4 left-gutter input-wrap">
			<label class="bold">Role</label>
			<span class="select-wrap">
				<select class="input-default small" name="role">
					<option value="administrator">Administrator</option>
					<option value="writer" selected>Writer</option>
				</select>
			</span>
		</div>

	</div>

	<div class="input-wrap">
		<button type="submit" class="button submit with-spinner">
			Invite User
			<span class="spinner1"></span>
			<span class="spinner2"></span>
		</button>
	</div>

</form>

<?php 
$allAuthors = adminAllUsers();
if (count($allAuthors) > 1)  : ?>
	<h3>Manage Authors</h3>
	<p class="info text-masked" style="margin-bottom:25px;">
		You can remove other authors here. Their post authorship will be transferred to the user who deletes them.
		Warning this cannot be undone.
	</p>
	<table class="horizontal">
		<thead><tr>
			<th align="left">Name</th>
			<th align="center">Email</th>
			<th align="center">Role</th>
			<th align="center">Status</th>
			<th align="right"></th>
		</tr></thead>
		<?php foreach ($allAuthors as $author) : ?>
			<?php if ($author['id'] !== adminGetUser()['id'] && (int)$author['id'] !== 1) : ?>
				<?php 
				$name   	   = $author['name'] === null ? '?': $author['name'];
				$slectedAdmin  = $author['role'] === 'administrator' ? 'selected' : '';
				$slectedWriter = $author['role'] === 'administrator' ? '' : 'selected';
				?>
				<tr>
					<td align="left"><?php echo $name;?></a></td>
					<td align="center"><?php echo $author['email'];?></td>
					<td align="center">
						<span class="select-wrap">
							<select data-author-id="<?php echo $author['id'];?>" class="input-default small js-change-role">
								<option value="administrator" <?php echo $slectedAdmin;?>>Administrator</option>
								<option value="writer" <?php echo $slectedWriter;?>>Writer</option>
							</select>
						<span>
					</td>
					<td align="center"><?php echo $author['status'];?></a></td>
					<td align="right">
						<a href="#" class="button mini-icon red delete-author js-delete-author" data-author-id="<?php echo $author['id'];?>">
							<span class="spinner1"></span>
							<span class="spinner2"></span>
							<svg viewBox="0 0 100 100"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#trash"></use></svg>
						</a>
					</td>
				</tr>
			<?php endif; ?>
		<?php endforeach; ?>
	</table>
	<div class="clearfix" style="height:20px"></div>
<?php endif; ?>