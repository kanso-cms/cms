<!-- PAGE CONTAINER -->
<section class="container-fluid">
	
	<!-- FORM CARD -->
	<div class="card accnt-form-card">

		<div class="pad-40">

			<!-- LOGO -->
			<div class="roof-xs floor-sm text-center">
				<svg class="logo" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
				<h1 class="roof-sm">Register</h1>
			</div>

			<!-- FORM -->
			<form class="js-validation-form <?php if ($IS_POST) echo 'danger'; ?>" method="post">
				<p class="color-gray">
					Complete your Kanso registration below with a username and password.
				</p>

				<!-- INPUTS -->
			    <div class="form-field row floor-xs">
			        <label for="username">Username</label>
			        <input type="text" name="username" id="username" data-js-required="true" class="js-mask-alpha-space"  data-js-min-length="5" value="<?php if (isset($_POST['username'])) echo $_POST['username']; ?>">
			        <p class="help-danger">* Please enter a username.</p>
			    </div>
			    
			    <div class="form-field row floor-xs">
			        <label for="name">Password</label>
			        <input type="password" name="password" id="password" data-js-required="true" data-js-validation="password" data-js-min-length="5">
			        <p class="help-danger">* Passwords must contain a special character or number.</p>
			    </div>

			    <!-- ACCESS TOKEN -->
			    <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN; ?>">
			    
				<!-- SUBMIT -->
			    <button type="submit" class="btn btn-primary btn-xl raised btn-block with-spinner">
			        <svg viewBox="0 0 64 64" class="loading-spinner"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg>
			        Register
			    </button>

			    <!-- FORM RESULT -->
			    <div class="form-result">
			        <div class="msg msg-danger" aria-hidden="true">
			            <div class="msg-icon">
			                <span class="glyph-icon glyph-icon-times icon"></span>
			            </div>
			            <div class="msg-body">
			                <p>There was a problem processing your request. Please try again later.</p>
			            </div>
			        </div>
			    </div>
			</form>

			<!-- OPTIONS -->
			<div class="text-center roof-xs">
				<a class="fancy-link p6" href="/admin/forgot-password/">Forgot your password?</a>
			</div>
		</div>
	</div>
	
</section>