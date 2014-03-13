<div class="img-hero overflow full-height img-hero-bar">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 white-text">
            <?php if ( isset( $_GET['reset_key'] ) ) : ?>
			<h3>Type your new password below</h3>
			<form id="fv_reset_password_key" role="form" method="post">
            	<input type="hidden" name="fv_reset_password_key" value="1" />
                <input type="hidden" name="reset_key" value="<?php echo $_GET['reset_key']; ?>" />
                <?php wp_nonce_field( 'fv_reset_password_key_nonce', 'fv_reset_password_key_nonce' ); ?>
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-key"></i></span>
						<input type="text" placeholder="new password" name="new_password" value="" class="lg-input full-width white">
				</div>
				<div class="form-group">
					<input type="submit" value="set password" name="set password" class="lg-input orange">
                    <span class="field-meta"><a href="<?php echo home_url(SF_Users::LOGIN_PATH); ?>">already a member? login here</a> | <a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>">register for an account</a></span>
				</div>
			</form>
            <?php else: ?>
            <h3>Enter your email and we'll send you a password reset link to your email address.</h3>
			<form id="fv_reset_password" role="form" method="post">
            	<input type="hidden" name="fv_reset_password" value="1" />
                <?php wp_nonce_field( 'fv_reset_password_nonce', 'fv_reset_password_nonce' ); ?>
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-envelope"></i></span>
						<input type="email" placeholder="email address" name="email" value="<?php echo $_POST['email']; ?>" class="lg-input full-width white">
				</div>
				<div class="form-group">
					<input type="submit" value="reset password" name="reset password" class="lg-input orange">
                    <span class="field-meta"><a href="<?php echo home_url(SF_Users::LOGIN_PATH); ?>">already a member? login here</a> | <a href="<?php echo home_url(SF_Users::REGISTER_PATH); ?>">register for an account</a></span>
				</div>
			</form>
            <?php endif; ?>
			</div>
		</div>
	</div>
</div>