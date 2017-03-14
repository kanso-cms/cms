<div class="col-12 col-md-8 roof-sm floor-sm">

	<h4>Invite authors</h4>
	<p class="color-gray">
		By default, signing up as an author to Kanso is barred for security reasons. However you can add other users
		by inviting them here. Kanso will send them an email with a special url to register.
	</p>

	<form method="post" class="js-validation-form floor-sm">

		<div class="row">
			<div class="col col-12 col-md-6 gutter-md-xs gutter-md-r">
				<div class="form-field row floor-sm">
		            <label for="invite_email">Email</label>
		            <input type="email" name="email" id="invite_email" placeholder="jappleseed@example.com" data-js-required="true" data-js-validation="email">
		            <p class="help-danger">* Please enter a valid email address.</p>
		        </div>
		    </div>

		    <div class="col col-12 col-md-6 gutter-md-xs gutter-md-l">
		        <div class="form-field row floor-sm">
		            <label for="invite_role">Role</label>
		            <select name="role" id="invite_role" data-js-required="true">
		            	<option value="" selected>Select role</option>
						<option value="administrator">Administrator</option>
						<option value="writer" >Writer</option>
					</select>
		            <p class="help-danger">* Please select a user role.</p>
		        </div>
		    </div>
		</div>
		
		<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
		<input type="hidden" name="form_name" value="invite_user">
	    <button type="submit" class="btn btn-success raised">Invite</button>

	</form>

	<h4>Manage Authors</h4>
	<?php if (count($all_authors) > 1)  : ?>
		<p class="color-gray">
			You can remove other authors here. Their post authorship will be transferred to the user who deletes them.
			Warning this cannot be undone.
		</p>
		<table class="table table-bordered">
			<thead><tr>
				<th>Name</th>
				<th>Email</th>
				<th>Status</th>
				<th>Role</th>
				<th align="right"></th>
			</tr></thead>
			<?php foreach ($all_authors as $author) : ?>
				<?php if ($author['id'] !== $ADMIN_INCLUDES->user()['id'] && (int)$author['id'] !== 1) : ?>
					<tr>
						<td><?php echo $author['name'] === null ? '?': $author['name'];?></a></td>
						<td><?php echo $author['email'];?></td>
						<td><?php echo $author['status'];?></a></td>
						<td>
							<span class="form-field">
								<select onchange="document.getElementById('status-form-<?php echo $author['id'];?>').submit()">
									<option value="administrator" <?php echo $author['role'] === 'administrator' ? 'selected' : '';?>>Administrator</option>
									<option value="writer" <?php echo $author['role'] === 'administrator' ? '' : 'selected'; ?>>Writer</option>
								</select>
							<span>
						</td>
						<td style="text-align: center;">
							<a href="#" class="btn btn-pure btn-danger btn-xs tooltipped tooltipped-n" data-tooltip="Delete user" onclick="document.getElementById('delete-form-<?php echo $author['id'];?>').submit()">
								<span class="glyph-icon glyph-icon-trash-o icon-md"></span>
							</a>
						</td>
					</tr>
					<form method="post" id="status-form-<?php echo $author['id'];?>" style="display: none">
						<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
						<input type="hidden" name="form_name"    value="change_user_role">
						<input type="hidden" name="user_id"      value="<?php echo $author['id'];?>">
						<input type="hidden" name="role"         value="<?php echo $author['role'] === 'administrator' ? 'writer' : 'administrator';?>">
					</form>
					<form method="post" id="delete-form-<?php echo $author['id'];?>" style="display: none">
						<input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
						<input type="hidden" name="form_name" value="delete_user">
						<input type="hidden" name="user_id"   value="<?php echo $author['id'];?>">
					</form>
				<?php endif; ?>
			<?php endforeach; ?>
		</table>
	<?php else : ?>
		<div class="msg msg-info">
            <div class="msg-icon">
                <span class="glyph-icon glyph-icon-info icon"></span>
            </div>
            <div class="msg-body">
                <p>You are currently the only author. When other authors are added you'll be able to manage them here.</p>
            </div>
        </div>
	<?php endif;?>

</div>