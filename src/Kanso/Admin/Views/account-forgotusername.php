<!-- PAGE CONTAINER -->
<section class="container-fluid">
	
	<!-- FORM CARD -->
	<div class="card accnt-form-card">

		<div class="pad-40">

			<!-- LOGO -->
			<div class="roof-xs floor-sm text-center">
				<svg class="logo" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
				<h1 class="roof-sm">Forgot Username</h1>
			</div>

			<!-- FORM -->
			<form class="js-validation-form <?php if ($IS_POST) echo 'success'; ?>" method="post">
			    <p class="color-gray tex">
					Enter the email address your Kanso account is registered to and we'll send you a reminder of your username.
				</p>

				<!-- INPUTS -->
			    <div class="form-field row floor-xs">
			        <label for="email">Email</label>
			        <input type="text" name="email" id="email" data-js-required="true" data-js-validation="email" value="<?php if (isset($_POST['email'])) echo $_POST['email'];?>">
			        <p class="help-danger">* Please enter a valid email address.</p>
			    </div>
			    
			    <!-- ACCESS TOKEN -->
			    <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">
			    
			    <!-- SUBMIT -->
			    <button type="submit" class="btn btn-primary btn-xl raised btn-block with-spinner">
			        <svg viewBox="0 0 64 64" class="loading-spinner"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg>
			        Send Reminder
			    </button>
			    
			    <!-- FORM RESULT -->
			    <div class="form-result">
			        <div class="msg msg-danger" aria-hidden="true">
			            <div class="msg-icon">
			                <span class="glyph-icon glyph-icon-times icon"></span>
			            </div>
			            <div class="msg-body">
			                <p>There was an error processing your request.</p>
			            </div>
			        </div>
			        <div class="msg msg-success" aria-hidden="true">
			            <div class="msg-icon">
			                <span class="glyph-icon glyph-icon-check icon"></span>
			            </div>
			            <div class="msg-body">
			                <p>If a user is registered under that email address, they were sent an email with their username.</p>
			            </div>
			        </div>
			    </div>
			</form>
			
			<div class="text-center roof-xs">
				<a class="fancy-link p6 inline-block float-left" href="/admin/login/">Back to login</a>
				<a class="fancy-link p6 inline-block float-right" href="/admin/forgot-password/">Forgot password?</a>
			</div>
		</div>
	</div>
	
</section>