<!-- PAGE CONTAINER -->
<section class="container-fluid">
	
	<!-- FORM CARD -->
	<div class="card accnt-form-card">

		<div class="pad-40">

			<!-- LOGO -->
			<div class="roof-xs floor-sm text-center">
				<svg class="logo" viewBox="0 0 512 512"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
				<h1 class="roof-sm">Login</h1>
			</div>

			<!-- FORM -->
			<form class="js-validation-form <?php if ($IS_POST) echo 'danger'; ?>" method="post">
			    
				<!-- INPUTS -->
			    <div class="form-field row floor-xs">
			        <label for="username">Username</label>
			        <input type="text" name="username" id="username" data-js-required="true" value="<?php if (isset($_POST['username'])) echo $_POST['username'];?>">
			        <p class="help-danger">* Please enter your username.</p>
			    </div>
			    
			    <div class="form-field row floor-xs">
			        <label for="name">Password</label>
			        <input type="password" name="password" id="password" data-js-required="true">
			        <p class="help-danger">* Please enter your password.</p>
			    </div>

			    <!-- ACCESS TOKEN -->
			    <input type="hidden" name="access_token" value="<?php echo $ACCESS_TOKEN;?>">

			    <!-- SUBMIT -->
			    <button type="submit" class="btn btn-primary btn-xl raised btn-block with-spinner">
			        <svg viewBox="0 0 64 64" class="loading-spinner"><circle class="path" cx="32" cy="32" r="30" fill="none" stroke-width="4"></circle></svg>
			        Login
			    </button>

			    <!-- FORM RESULT -->
			    <div class="form-result">
			        <div class="msg msg-danger" aria-hidden="true">
			            <div class="msg-icon">
			                <span class="glyph-icon glyph-icon-times icon"></span>
			            </div>
			            <div class="msg-body">
			                <p>Either your username or password was incorrect.</p>
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