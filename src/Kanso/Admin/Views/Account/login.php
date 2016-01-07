	<div class="site-container cleafix login setup-panel">
		<div class="panel">
			<div class="logo-wrap">
				<svg viewBox="0 0 512 512" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
			</div>

			<form class="ajax-form js-login-form">
				
				<div class="input-wrap">
					<label class="bold">Username</label>
					<input class="input-default" type="text" name="username" data-js-required="true" value="" placeholder="John-Appleseed"/>
				</div>

				<div class="input-wrap">
					<label class="bold">Password</label>
					<input class="input-default" type="password" name="password"  data-js-required="true" value="" placeholder="•••••••••" />
				</div>

				<button type="submit" class="button submit">
					Login
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</button>

				<div class="form-result">
					<div class="error message flipInX">
						<div class="message-icon">
							<svg viewBox="0 0 100 100" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#error"></use></svg>
						</div>
						<div class="message-body">
							<p>Either your username or password was incorrect.</p>
						</div>
					</div>
				</div>

			</form>
			<p class="info"><a href="/admin/forgot-password">Forgot your password?</a></p>
		</div>
	</div>