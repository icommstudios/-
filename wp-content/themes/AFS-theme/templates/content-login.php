<div class="img-hero overflow full-height img-hero-bar">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 white-text">
			<h3>Login to your account</h3>
			<form id="fv_login" role="form" method="post">
            	<input type="hidden" name="fv_login" value="1" />
                <input type="hidden" name="redirect_to" value="<?php echo stripslashes($_REQUEST['redirect_to']); ?>" />
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-envelope"></i></span>
						<input type="email" placeholder="email address" value="<?php echo $_POST['email']; ?>" name="email" class="lg-input full-width white">
				</div>
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-key"></i></span>
					<input type="password" name="password" placeholder="enter password" class="lg-input full-width white">
				</div>
				<div class="form-group">
					<div class="custom-checkbox">
						<input type="checkbox" value="1" <?php echo ($_POST['remember_me']) ? 'checked="checked"' : ''; ?> name="remember_me" id="remember_me" class="lg-input white">
						<label for="remember_me"></label><span class="field-meta">remember me</span>
					</div>
				</div>
				<div class="form-group">
                	<?php wp_nonce_field( 'fv_login_action', 'fv_login' ); ?>
					<input type="submit" value="login" name="subscribe" class="lg-input orange">
					<span class="field-meta"><a href="<?php echo home_url(SF_Users::RESET_PASSWORD_PATH); ?>">forgot password?</a> | <a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>">register for an account</a></span>
				</div>
			</form>
			</div>
		</div>
	</div>
</div>