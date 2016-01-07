	<div class="site-container cleafix reset-password setup-panel">
		<div class="panel">
			<div class="logo-wrap">
				<svg viewBox="0 0 512 512" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
			</div>

			<form class="ajax-form js-reset-password-form">
				
				<div class="input-wrap">
					<p class="info text-masked text-center">Enter in a new password for your Kanso account.</p>
				</div>

				<div class="input-wrap">
					<label class="bold">Password</label>
					<input class="input-default" type="password" name="password" data-js-required="true" data-js-min-legnth="6" data-js-validation="password" data-js-max-legnth="100" maxlength="100" value="" placeholder="•••••••••" autocomplete="off"/>
					<p class="input-error">* Your password needs to be more than six characters long, including at least one number or special character.</p>
				</div>

				<button type="submit" class="button submit">
					Reset Password
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</button>

				<div class="form-result">
					<div class="success message flipInX">
						<div class="message-icon">
							<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#success"></use></svg>
						</div>
						<div class="message-body">
							<p>Your password was successfully reset.</p>
						</div>
					</div>
					<div class="error message flipInX">
						<div class="message-icon">
							<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#error"></use></svg>
						</div>
						<div class="message-body">
							<p>There was an error processing your request.</p>
						</div>
					</div>
				</div>

			</form>
			<p class="info"><a href="/admin/login">Back to login</a></p>
		</div>
	</div>