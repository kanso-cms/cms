	<div class="site-container cleafix register setup-panel">
		<div class="panel">
			<div class="logo-wrap">
				<svg viewBox="0 0 512 512" class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="#logo"></use></svg>
			</div>

			<form class="ajax-form js-register-form">

				<div class="input-wrap">
					<p class="info text-masked text-center">Enter your details to complete your registration.</p>
				</div>
				
				<div class="input-wrap">
					<label class="bold">Username</label>
					<input class="input-default" type="text" name="username" data-js-required="true" data-js-validation="no-spaces-text" data-js-min-legnth="5" data-js-max-legnth="30" maxlength="30" value="" placeholder="John-Appleseed" autocomplete="off"/>
					<p class="input-error">* Your username needs to be at least five characters long, no spaces or special characters.</p>
				</div>

				<div class="input-wrap">
					<label class="bold">Email</label>
					<input class="input-default" type="text" name="email" data-js-validation="email" data-js-max-legnth="100" data-js-required="true" maxlength="100" value="" placeholder="John@Appleseed.com" autocomplete="off"/>
					<p class="input-error">* Please enter a valid email address.</p>
				</div>

				<div class="input-wrap">
					<label class="bold">Password</label>
					<input class="input-default" type="password" name="password" data-js-required="true" data-js-min-legnth="6" data-js-validation="password" data-js-max-legnth="100" maxlength="100" value="" placeholder="•••••••••" autocomplete="off"/>
					<p class="input-error">* Your password needs to be more than six characters long, including at least one number or special character.</p>
				</div>

				<button type="submit" class="button submit">
					Register
					<span class="spinner1"></span>
					<span class="spinner2"></span>
				</button>

				<div class="form-result">
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
		</div>
	</div>