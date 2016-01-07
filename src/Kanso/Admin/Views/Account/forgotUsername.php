	<div class="site-container cleafix forgot-username setup-panel">
		<div class="panel">
			<div class="logo-wrap">
				<svg viewBox="0 0 512 512" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
			</div>

			<form class="ajax-form js-forgot-username-form">
				
				<div class="input-wrap">
					<p class="info text-masked">Enter the email address associated with your Kanso account.</p>
				</div>

				<div class="input-wrap">
					<label class="bold">Email</label>
					<input class="input-default" type="text" name="email" data-js-validation="email" data-js-max-legnth="100" data-js-required="true" maxlength="100" value="" placeholder="John@Appleseed.com"/>
					<p class="input-error">* Please enter a valid email address.</p>
				</div>

				<button type="submit" class="button submit">
					Send Reminder
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</button>

				<div class="form-result">
					<div class="info message flipInX">
						<div class="message-icon">
							<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#info"></use></svg>
						</div>
						<div class="message-body">
							<p>If a user is registered with that email address, they were sent an email a username reminder.</p>
						</div>
					</div>
				</div>

			</form>
			<p class="info">
				<a class="text-left col col-6 no-gutter"href="/admin/login">Back to login</a>
				<a class="text-right col col-6 no-gutter" href="/admin/forgot-password">Forgot your password?</a>
			</p>
		</div>
	</div>