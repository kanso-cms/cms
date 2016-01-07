	<div class="site-container cleafix forgot-password setup-panel">
		<div class="panel">
			<div class="logo-wrap">
				<svg viewBox="0 0 512 512" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
			</div>

			<form class="ajax-form js-forgot-password-form">
				
				<div class="input-wrap">
					<p class="info text-masked">Enter your Kanso user-name to reset your password.</p>
				</div>

				<div class="input-wrap">
					<label class="bold">Username</label>
					<input class="input-default" type="text" name="username" data-js-required="true" value="" placeholder="John-Appleseed"/>
				</div>

				<button type="submit" class="button submit">
					Reset Password
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</button>

				<div class="form-result">
					<div class="info message flipInX">
						<div class="message-icon">
							<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#info"></use></svg>
						</div>
						<div class="message-body">
							<p>If a user is registered under that user-name, they were sent an email with reset instructions.</p>
						</div>
					</div>
				</div>

			</form>
			<p class="info">
				<a class="text-left col col-6 no-gutter"href="/admin/login">Back to login</a>
				<a class="text-right col col-6 no-gutter" href="/admin/forgot-username">Forgot your username?</a>
			</p>
		</div>
	</div>