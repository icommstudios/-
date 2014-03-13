<?php
//Is this a normal registration or claim your listing? 
$is_claim_listing = (isset($_GET['claim_listing'])) ? TRUE : FALSE;
$claim_listing_title = '';
$preset_email = '';
if ( $is_claim_listing ) {
	$claim_data = json_decode(base64_decode($_GET['claim_listing']), true);
	if ( $claim_data['listing_id'] && get_post_type($claim_data['listing_id']) )  {
		$claim_listing_title = get_the_title($claim_data['listing_id']);
		$preset_email = get_post_meta($claim_data['listing_id'], '_email', true);
	}
}
?>

<div class="img-hero overflow full-height img-hero-bar">
	<div class="container">
		<div class="row">
			<div class="col-lg-6 white-text">
            <?php if ( $is_claim_listing ) : ?>
			<h3>Claim your listing: <?php echo $claim_listing_title; ?></h3>
            <h3>Register an account below to claim your new AFS account Listing!</h3>
            <?php else : ?>
            <h3>Register your new AFS account</h3>
            <?php endif; ?>
			<form id="fv_register" role="form" method="post">
            	<input type="hidden" name="fv_register" value="1" />
                <input type="hidden" name="redirect_to" value="<?php echo stripslashes($_REQUEST['redirect_to']); ?>" />
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-envelope"></i></span>
						<input type="email" placeholder="email/username" value="<?php echo ($_POST['email']) ? $_POST['email'] : $preset_email; ?>" name="email" class="lg-input white full-width clear-val white">
				</div>
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-key"></i></span>
					<input type="password" name="password" placeholder="enter password" class="lg-input white full-width white">
				</div>
				<?php if ( $is_claim_listing ) : ?>
                	<input type="hidden" name="claim_listing" value="<?php echo trim($_GET['claim_listing']); ?>" />
                <?php else : ?>
				<div class="form-group addon-icon">
					<span class="add-on"><i class="fa fa-user"></i></span>
					<label class="custom-select">
						<select name="type" class="lg-select full-width white">
						  <option value="<?php echo SF_Users::USER_TYPE_CONTRACTOR; ?>" <?php echo ($_POST['type'] == SF_Users::USER_TYPE_CONTRACTOR) ?  'selected="selected"' : ''; ?>>I am a trusted contractor</option>
						  <option value="<?php echo SF_Users::USER_TYPE_FACILITY; ?>" <?php echo ($_POST['type'] == SF_Users::USER_TYPE_FACILITY) ? 'selected="selected"' : ''; ?>>I am an honest facility manager</option>
						</select>
					</label>
				</div>
				<div class="form-group">
					<input name="company" placeholder="company name (listing name)" value="<?php echo $_POST['company']; ?>" class="lg-input white full-width white">
				</div>
				<div class="form-group">
					<input name="phone" placeholder="phone number" value="<?php echo $_POST['phone']; ?>" class="lg-input white full-width white">
				</div>
				<div class="form-group">
					<input name="website" placeholder="website url" value="<?php echo $_POST['website']; ?>" class="lg-input white full-width white">
				</div>
                <?php endif; ?>
                <div class="form-group">
					<div class="custom-checkbox">
						<input type="checkbox" value="1" <?php echo ( $_POST['agree_terms'] ) ? 'checked="checked"' : ''; ?> name="agree_terms" id="agree_terms" class="white"/>
						<label for="agree_terms"></label><span class="field-meta">I accept the AFS Terms of Use</span>
					</div>
				</div>
				<div class="form-group">
                   <?php if ( $is_claim_listing ) : ?>
					<input type="submit" value="claim your listing" name="register" class="lg-input orange">
                   <?php else : ?>
                    <input type="submit" value="register" name="register" class="lg-input orange">
                   <?php endif; ?>
					<span class="field-meta"><a href="<?php echo home_url(SF_Users::LOGIN_PATH); ?>">already a member? login here</a> | <a href="<?php echo home_url(SF_Users::RESET_PASSWORD_PATH); ?>">forgot password?</a></span>
				</div>
			</form>
			</div>
		</div>
	</div>
</div>