<?php

class SF_Users extends SF_FV {
	
	const LOGIN_PATH = 'login';
	const REGISTER_PATH = 'register';
	const RESET_PASSWORD_PATH = 'reset-password';
	
	const PROFILE_PATH_FACILITY = 'facility-profile';
	const PROFILE_PATH_CONTRACTOR = 'vendor-profile';
	
	const USER_TYPE_META_KEY =  'fv_user_type';
	const USER_TYPE_ID_META_KEY =  'fv_user_type_id';
	const USER_TYPE_FACILITY =  'facility';
	const USER_TYPE_CONTRACTOR =  'contractor';
	
	static $url_paypal_ipn;
	static $url_paypal_return;
	static $url_paypal_cancel;
	static $url_paypal_api_endpoint;
	
	static $facility_membership_types = array(
		'F1' => array('cost' => '199.00', 'label' => 'AFS Facility Vendor Membership', 'type' => 'main', 'description' => 'Facility Vendor Membership'),
		'FC1' => array('cost' => '25.00', 'label' => 'AFS - 1 Additional Category', 'type' => 'addon', 'description' => '1 Additional Category', 'data' => 1),
		'FC2' => array('cost' => '50.00', 'label' => 'AFS - 2 Additional Categories', 'type' => 'addon', 'description' => '2 Additional Categories', 'data' => 2),
		'FC3' => array('cost' => '75.00', 'label' => 'AFS - 3 Additional Categories', 'type' => 'addon', 'description' => '3 Additional Categories', 'data' => 3),
		'FC4' => array('cost' => '100.00', 'label' => 'AFS - 4 Additional Categories', 'type' => 'addon', 'description' => '4 Additional Categories', 'data' => 4),
		'FC5' => array('cost' => '125.00', 'label' => 'AFS - 5 Additional Categories', 'type' => 'addon', 'description' => '5 Additional Categories', 'data' => 5),
	);
	
	static $contractor_membership_types = array(
		'C1' => array('cost' => '199.00', 'label' => 'AFS Contractor Membership', 'type' => 'main', 'description' => 'Contractor Membership'),
		'CC1' => array('cost' => '25.00', 'label' => 'AFS - 1 Additional Category', 'type' => 'addon', 'description' => '1 Additional Category', 'data' => 1),
		'CC2' => array('cost' => '50.00', 'label' => 'AFS - 2 Additional Categories', 'type' => 'addon', 'description' => '2 Additional Categories', 'data' => 2),
		'CC3' => array('cost' => '75.00', 'label' => 'AFS - 3 Additional Categories', 'type' => 'addon', 'description' => '3 Additional Categories', 'data' => 3),
		'CC4' => array('cost' => '100.00', 'label' => 'AFS - 4 Additional Categories', 'type' => 'addon', 'description' => '4 Additional Categories', 'data' => 4),
		'CC5' => array('cost' => '125.00', 'label' => 'AFS - 5 Additional Categories', 'type' => 'addon', 'description' => '5 Additional Categories', 'data' => 5),
	);
	
	private static $instance;

	public static function init() {
		
		//Setup vars
		self::$url_paypal_ipn = add_query_arg(array('fv_ipn_listener' => 1), trailingslashit(home_url()) );
		self::$url_paypal_return = add_query_arg(array('fv_pay_return' => 1), trailingslashit(home_url()) );
		self::$url_paypal_cancel = add_query_arg(array('fv_pay_cancel' => 1), trailingslashit(home_url()) );
		//self::$url_paypal_api_endpoint = 'https://www.paypal.com/cgi-bin/webscr'; //Live
		self::$url_paypal_api_endpoint = 'https://www.sandbox.paypal.com/cgi-bin/webscr'; //Sandbox
		
		//Handle actions
		
		//Login
		add_action( 'wp_loaded', array( get_class(), 'handle_login'), 10);
		add_filter( 'authenticate', array( get_class(), 'email_authenticate'), 20, 3 );
		add_action( 'template_redirect', array( get_class(), 'redirect_users_from_login_form'), 20 ); // if user already logged in
		add_action( 'wp_login_failed', array( get_class(), 'login_failed' ), 10, 1 );	// Hooked login
		add_action( 'wp_loaded', array( get_class(), 'redirect_away_from_login' ) ); // redirect from wp-login.php
		add_filter( 'login_url', array( get_class(), 'login_url' ), 10, 2 ); // Replace WP Login URIs
		add_filter( 'logout_url' , array( get_class(), 'log_out_url' ), 100, 2 ); // Replace WP Login URIs
		
		//Register
		add_action( 'wp_loaded', array( get_class(), 'handle_register'), 10);
		add_action( 'template_redirect', array( get_class(), 'redirect_users_from_register_form'), 20 ); // if user already logged in
		add_filter( 'register_url', array( get_class(), 'register_url' ), 10, 1 ); // Replace WP Regiser URIs
		
		//Reset Password
		add_action( 'wp_loaded', array( get_class(), 'handle_reset_password'), 10);
		add_action( 'template_redirect', array( get_class(), 'redirect_users_from_reset_password_form'), 20 ); // if user already logged in
		add_filter( 'lostpassword_url', array( get_class(), 'reset_password_url' ), 10, 2 );
		
		//Profile Edit
		add_action( 'template_redirect', array( get_class(), 'load_profile_edit'), 10);
		
		//Membership payments, IPN Listener
		add_action( 'wp_loaded', array( get_class(), 'handle_ipn_calls'), 0);
		
		// Admin view of User
		add_action( 'show_user_profile', array( get_class(), 'show_admin_profile_fields') );
		add_action( 'edit_user_profile', array( get_class(), 'show_admin_profile_fields') );
		add_action( 'personal_options_update', array( get_class(), 'save_admin_profile_fields') );
		add_action( 'edit_user_profile_update', array( get_class(), 'save_admin_profile_fields') );
		
	}
	
	// Reset Password
	public function reset_password_url() {
		$url = home_url(self::RESET_PASSWORD_PATH);
		return $url;
	}
	
	public static function is_reset_password_page() {
		return is_page_template( 'template-send-password.php' );
	}
	
	public static function redirect_users_from_reset_password_form() {
		if ( self::is_reset_password_page() && is_user_logged_in() ) {
			// Registered users shouldn't be here. Send them elsewhere
			if ( is_user_logged_in() ) {
				wp_redirect( home_url(), 303 );
				exit();
			}
		}
		//Check for valid reset key
		if ( self::is_reset_password_page() && isset( $_GET['reset_key'] ) ) {
			if ( !self::valid_reset_password( $_GET['reset_key'] ) ) {
				
				// invalid password reset key
				self::set_message( self::__('The Password Reset key is invalid. Please try again later.'), self::MESSAGE_STATUS_ERROR );
				wp_redirect( add_query_arg( array( 'message' => 'invalidkey' ), home_url(self::RESET_PASSWORD_PATH) ) );
				exit();
			}
		
		}
	}
	
	public function handle_reset_password() {
		if ( !empty( $_POST['fv_reset_password'] ) && wp_verify_nonce( $_POST['fv_reset_password_nonce'], 'fv_reset_password_nonce' ) ) {
			self::process_reset_password_form();
			return;
		}
		if ( !empty( $_POST['fv_reset_password_key'] ) && wp_verify_nonce( $_POST['fv_reset_password_key_nonce'], 'fv_reset_password_key_nonce' ) ) {
			self::process_user_reset_password_key();
			return;
		}
	}
	
	public static function process_reset_password_form() {
		
		// Registered users shouldn't be here. Send them elsewhere
		if ( is_user_logged_in() ) {
			wp_redirect( home_url(), 303 );
			exit();
		}

		// Lookup reset
		$result = self::retrieve_user_reset_key();
		
		if ( $result == 'confirm' ) {
			self::set_message( self::__('We have sent password reset instructions to your email address. Please follow the instructions to set your new password.'), self::MESSAGE_STATUS_SUCCESS );
			wp_redirect( add_query_arg( array( 'message' => $result ), home_url() ) );
			exit();
		} elseif ( $result == 'invalid' ) {
			self::set_message( self::__('Sorry, we could not find a user with that email address.'), self::MESSAGE_STATUS_ERROR );
		} elseif ( $result == 'no_email' ) {
			self::set_message( self::__('Please type your email address.'), self::MESSAGE_STATUS_ERROR );
		} elseif ( $result == 'not_allowed' ) {
			self::set_message( self::__('Sorry, password reset has been disabled.'), self::MESSAGE_STATUS_ERROR );
		}
		//wp_redirect( add_query_arg( array( 'message' => $result ), home_url(self::RESET_PASSWORD_PATH) ) );
		//exit();
	}
	
	public static function process_user_reset_password_key() {

		// Registered users shouldn't be here. Send them elsewhere
		if ( is_user_logged_in() ) {
			wp_redirect( home_url(), 303 );
			exit();
		}

		// Is Reset Attempt
		if ( $_POST['new_password'] ) {
			
			//Lookup user for password reset key
			$user = self::valid_reset_password( $_POST['reset_key'] );
			if ( !$user ) {
				// invalid password reset key
				self::set_message( self::__('The Password Reset key is invalid. Please try again later.'), self::MESSAGE_STATUS_ERROR );
				wp_redirect( add_query_arg( array( 'message' => 'invalid_key' ), home_url(self::RESET_PASSWORD_PATH) ) );
				exit();
			}
			
			$new_password = $_POST['new_password'];
			
			//Set the new password
			wp_set_password( $new_password, $user->ID );
			$user = wp_signon(
				array(
					'user_login' => $user->user_login,
					'user_password' => $new_password,
					'remember' => true
				), false );
	
			$data = array(
				'user' => $user,
				'new_password' => $new_password
			);
	
			//do_action( 'password_reset_notification', $data );
	
			wp_password_change_notification( $user );
			
			//self::set_message( self::__('Success! Your password has been set.'), self::MESSAGE_STATUS_SUCCESS );
			wp_redirect( add_query_arg( array( 'message' => 'new_pass' ), SF_Users::user_profile_url($user->ID)) );
			exit();
		}
		
	}
	
	// Lookup user for reset key
	private static function retrieve_user_reset_key() {
		global $wpdb;

		if ( empty( $_POST['email'] ) ) {
			return 'no_email';
		}

		//Lookup user for email
		if ( strpos( $_POST['email'], '@' ) ) {
			$user_data = get_user_by( 'email', trim( $_POST['email'] ) );
		} else {
			return 'no_email';
		}
		
		if ( empty( $user_data ) ) return 'invalid';

		// Get the user details from the looked up user 
		$user_login = $user_data->user_login;
		$user_email = $user_data->user_email;
		
		$allow = apply_filters( 'allow_password_reset', TRUE, $user_data->ID );

		if ( !$allow ) {
			return 'not_allowed';
		}

		$key = $wpdb->get_var( $wpdb->prepare( "SELECT user_activation_key FROM $wpdb->users WHERE user_login = %s", $user_login ) );

		if ( empty( $key ) ) {
			// Generate a new key (using generate password to generate md5 hash)
			$key = wp_generate_password( 20, false );
			$wpdb->update( $wpdb->users, array( 'user_activation_key' => $key ), array( 'user_login' => $user_login ) );
		}
		
		$reset_link = add_query_arg( array('reset_key' => $key ), home_url(self::RESET_PASSWORD_PATH) );
		
		//Send email
		$email_replace_keys = array('user_email' => $user_email, 'site_name' => get_option('blogname'), 'site_url' => home_url(), 'reset_link' => $reset_link );
		$email_data = array(
			'to_email' => $user_email,
			'from_email' => self::$notification_from_email,
			'from_name' => self::$notification_from_name,
			'subject' => self::build_email_subject('reset_password', $email_replace_keys),
			'content' => self::build_email_content('reset_password', $email_replace_keys),
			'is_html' => self::$notification_format_is_html
		);
		
		$result = SF_FV::send_email($email_data);

		return 'confirm';
	}

	// Check for a valid reset key
	private static function valid_reset_password( $key ) {
		global $wpdb;

		$key = preg_replace( '/[^a-z0-9]/i', '', $key );

		if ( empty( $key ) || is_array( $key ) )
			return false;

		$user = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM $wpdb->users WHERE user_activation_key = %s", $key ) );

		if ( empty( $user ) )
			return false;
			
		return $user;
	}
	
	// Add Customer ID field to Users Admin
	public function show_admin_profile_fields( $user ) { 
		if ( is_admin() ) {
		?>
	
		<h3>FV Account Type for User ID <?php echo $user->ID; ?></h3>
	
		<table class="form-table">
	
			<tr>
				<th><label for="<?php echo self::USER_TYPE_META_KEY; ?>">Account Type</label></th>
	
				<td>
					<input type="text" name="<?php echo self::USER_TYPE_META_KEY; ?>" id="<?php echo self::USER_TYPE_META_KEY; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, self::USER_TYPE_META_KEY, TRUE ) ); ?>" class="medium-text" /> <em> Options are: 
					<?php echo self::USER_TYPE_FACILITY.' or '.self::USER_TYPE_CONTRACTOR; ?></em>
				</td>
			</tr>
            <tr>
				<th><label for="<?php echo self::USER_TYPE_ID_META_KEY; ?>">Type ID</label></th>
	
				<td>
					<input type="text" name="<?php echo self::USER_TYPE_ID_META_KEY; ?>" id="<?php echo self::USER_TYPE_ID_META_KEY; ?>" value="<?php echo esc_attr( get_user_meta( $user->ID, self::USER_TYPE_ID_META_KEY, TRUE ) ); ?>" class="small-text" /> <em> The Post ID for the Account Type above</em>
				</td>
			</tr>
            <tr>
				<th><label for="associated_recrds">Associated Records</label></th>
	
				<td>
					<?php
					$record_post_id = get_user_meta( $user->ID, self::USER_TYPE_ID_META_KEY, TRUE );
					if ( $record_post_id ) {
						echo get_the_title($record_post_id);
						echo ' ('.get_user_meta( $user->ID, self::USER_TYPE_META_KEY, TRUE ).') ';
						echo '<br><a href="'.get_edit_post_link($record_post_id).'">EDIT</a> | ';
						echo '<a href="'.get_permalink($record_post_id).'">VIEW PROFILE</a> ';
					} else {
						echo 'None';	
					}
					?>
				</td>
			</tr>
	
		</table>
	<?php }
	}
	
	public function save_admin_profile_fields( $user_id ) {
		if ( is_admin() ) {
			if ( !current_user_can( 'edit_user', $user_id ) )
				return false;
		
			//Save Changes
			update_user_meta( $user_id, self::USER_TYPE_META_KEY, $_POST[self::USER_TYPE_META_KEY] );
			update_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, $_POST[self::USER_TYPE_ID_META_KEY] );
		}
	}
	
	// Profile url
	public function user_profile_url($user_id = null) {
		if ( is_user_logged_in() || $user_id ) {
			$lookup_user_id = ( $user_id ) ? $user_id : get_current_user_id();
			$user_type = get_user_meta( $lookup_user_id, self::USER_TYPE_META_KEY, true);
			
			if ( $user_type == self::USER_TYPE_FACILITY) {
				return home_url(self::PROFILE_PATH_FACILITY);
			} elseif ( $user_type == self::USER_TYPE_CONTRACTOR) {
				return home_url(self::PROFILE_PATH_CONTRACTOR);
			}
		}
		return home_url();
	}
	
	// Allow Login with Email address
	public function email_authenticate( $user, $username, $password ) {
		$user = get_user_by_email( $username );
		if ( $user ) $username = $user->user_login;
		return wp_authenticate_username_password( null, $username, $password );
	}

	public static function handle_login() {
		
		// Login Attempt
		if ( !empty( $_POST['fv_login'] ) && wp_verify_nonce( $_POST['fv_login'], 'fv_login_action' ) ) {
			$creds = array();
			$creds['user_login'] = $_POST['email'];
			$creds['user_password'] = $_POST['password'];
			$creds['remember'] = ($_POST['remember_me']) ? TRUE : FALSE;
			$user = wp_signon($creds);
			if ( !is_wp_error( $user ) ) {
				$user_id = $user->ID;
				
				if ( isset( $_POST['redirect_to'] ) && !empty( $_POST['redirect_to'] ) ) {
					$redirect_str = str_replace( home_url(), '', $_POST['redirect_to'] ); // in case the home_url is already added
					$redirect = home_url( $redirect_str );
					wp_redirect( $redirect );
				} else {
					wp_redirect( home_url() );
				}
				exit();
			}
		}
		
		// Handle Logout attempt
		elseif ( self::log_out_attempt() ) {
			// logout
			wp_logout();

			if ( isset( $_GET['redirect_to'] ) ) {
				$redirect_to = add_query_arg( array( 'loggedout' => 'true', 'message' => 'loggedout' ), home_url( $_GET['redirect_to'] ) );
			} else {
				$redirect_to = add_query_arg( array( 'loggedout' => 'true', 'message' => 'loggedout' ), home_url() );
			}
			wp_redirect( $redirect_to );
			exit();
		} 

	}
	
	public function redirect_users_from_login_form() {
		
		// Registered users shouldn't be here. Send them elsewhere
		if ( self::is_login_page() && is_user_logged_in() && !self::log_out_attempt() ) {
			wp_redirect( home_url(), 303 );
			exit();
		}
	}
	
	public static function is_login_page() {
		return is_page_template( 'template-login.php' );
	}

	
	// Errors
	public static function login_failed( $username ) {
		// recap a lot of wp-login.php
		if ( !empty( $_GET['loggedout'] ) )
			return;

		// If cookies are disabled we can't log in even with a valid user+pass
		if ( isset( $_POST['testcookie'] ) && empty( $_COOKIE[TEST_COOKIE] ) )
			$message = self::__( 'Cookies are Disabled' );

		if ( isset( $_GET['registration'] ) && 'disabled' == $_GET['registration'] )
			$message = self::__( 'Registration Disabled' );
		elseif ( isset( $_GET['checkemail'] ) && 'registered' == $_GET['checkemail'] )
			$message = self::__( 'Registered' );
		elseif ( isset( $_REQUEST['interim-login'] ) )
			$message = self::__( 'Error: Expired' );
		else
			$message = self::__( 'Username and/or Password Incorrect.' );

		$url = home_url(self::LOGIN_PATH);
		$url = add_query_arg( 'message', $message, $url );
		if ( isset( $_REQUEST['redirect_to'] ) ) {
			$url = add_query_arg( 'redirect_to', $_REQUEST['redirect_to'], $url );
		}
		self::set_message( $message, self::MESSAGE_STATUS_ERROR );
		//wp_redirect( $url );
		//exit();
	}


	public static function login_url( $url, $redirect ) {
		$url = home_url(self::LOGIN_PATH);
		if ( $redirect ) {
			$redirect = str_replace( home_url(), '', $redirect );
			$url = add_query_arg( 'redirect_to', $redirect, $url );
		} else {
			$redirect = str_replace( home_url(), '', $url );
			$url = add_query_arg( 'redirect_to', $redirect, $url );
		}
		return $url;
	}

	public static function log_out_url(  $url = null, $redirect = null ) {
		$url = home_url(self::LOGIN_PATH);
		if ( $redirect ) {
			$redirect = str_replace( home_url(), '', $redirect );
			$url = add_query_arg( array( 'redirect_to' => $redirect, 'action' => 'logout', 'message' => 'loggedout' ), $url );
		} else {
			$url = add_query_arg( array( 'action' => 'logout', 'message' => 'loggedout' ), $url );
		}
		return $url;
	}

	public static function log_out_attempt() {
		return ( isset( $_GET['action'] ) && 'logout' == $_GET['action'] ) ? TRUE : FALSE;
	}

	/**
	 * Redirects away from the login page.
	 *
	 */
	public function redirect_away_from_login() {
		global $pagenow;

		// check if it's part of a flash upload.
		if ( isset( $_POST ) && !empty( $_POST['_wpnonce'] ) )
			return;

		// always redirect away from wp-login.php but check if the user is an admin before redirecting them.
		if ( 'wp-login.php' == $pagenow || 'wp-activate.php' == $pagenow || 'wp-signup.php' == $pagenow || ( !current_user_can( 'edit_posts' ) && is_admin() && !defined( 'DOING_AJAX' ) ) ) {
			// If they're logged in, direct to the account page
			if ( is_user_logged_in() ) {
				wp_redirect( home_url() );
				exit();
			} else { // everyone else needs to login
				if ( !defined( 'DOING_AJAX' ) ) {
					$redirect = ( isset( $_GET['action'] ) ) ? add_query_arg( array( 'action' => $_GET['action'] ), home_url(self::LOGIN_PATH) ) : home_url(self::LOGIN_PATH) ;
					wp_redirect( $redirect );
					exit();
				}
			}
		}
	}
	
	// Handle Register
	
	public function handle_register() {
		if ( isset( $_POST['fv_register'] ) ) {
			self::process_register_form();
			return;
		}
	}
	
	private function process_register_form() {
		$errors = array();
		$email_address = isset( $_POST['email'] )?$_POST['email']:'';
		$username = $email_address; //username is email
		$password = isset( $_POST['password'] )?$_POST['password']:'';
		$password2 = isset( $_POST['password'] )?$_POST['password']:''; //same as password ( form doesn't ask for it twice )
		$type = isset( $_POST['type'] )?$_POST['type']:''; //account type
		$agree_terms = isset( $_POST['agree_terms'] )?$_POST['agree_terms']:''; //agree terms
		$company = isset( $_POST['company'] )?$_POST['company']:''; //company (also used for listing name)
		$phone = isset( $_POST['phone'] )?$_POST['phone']:''; //phone
		$website = isset( $_POST['website'] )?$_POST['website']:''; //website
		
		//Type of registration (is this a claim your listing)
		$is_claim_listing = FALSE; //if false, then this is a normal registration
		if ( isset($_POST['claim_listing']) && !empty($_POST['claim_listing']) ) {
			$is_claim_listing = TRUE;
		}
		
		$sanitized_user_login = sanitize_user( $username );
		$email_address =  trim($email_address);
		
		// Check the e-mail address
		if ( empty($email_address) ) {
			$errors[ 'empty_email'] = self::__( 'Please type your e-mail address.' );
		} elseif ( ! is_email( $email_address ) ) {
			$errors['invalid_email'] = self:: __( 'The email address isn&#8217;t correct.' );
			$email_address = '';
		} elseif ( email_exists( $email_address ) ) {
			$errors['email_exists'] = self::__( 'This email is already registered, please choose another one.' );
		}
		
		// check Password
		if ( $password == '' || $password2 == '' ) {
			$errors['empty_password'] = self::__( 'Please enter a password.' );
		} elseif ( $password != $password2 ) {
			$errors['password_mismatch'] = self:: __( 'Passwords did not match.' );
		}
		
		// Check the username but dont display errors about the username (we use the email address for the username)
		if ( empty($sanitized_user_login) ) {
			//$errors['empty_username'] = self::__( 'Please enter a username.' );
		} elseif ( ! validate_username( $sanitized_user_login ) ) {
			//$errors['invalid_username'] = self::__( 'This username is invalid because it uses illegal characters. Please enter a valid username.' );
			$sanitized_user_login = '';
			$errors['invalid_username'] = self::__( 'Your email could be used as your username.' );
		} elseif ( username_exists( $sanitized_user_login ) ) {
			//$errors['username_exists'] = self::__( 'This username is already registered, please choose another one.' );
			$errors['username_exists'] = self::__( 'Your email could not be used as your username because it is already in use.' );
		}
		
		//Check agree terms
		if ( empty($agree_terms) ) {
			$errors['empty_agree_terms'] = self::__( 'You must agree to our Terms of Use to continue.' );
		}
		
		//Validate based on type of registration
		if ( $is_claim_listing ) {
			
			//Decode
			$claim_data = json_decode(base64_decode($_POST['claim_listing']), true);
			if ( !empty($claim_data['listing_id']) && !empty($claim_data['listing_id']) && get_post_type($claim_data['listing_id']) ) {
				//Valid
			} else {
				//Missing claim fields
				$errors[ 'missing_claim_fields'] = self::__( 'Could not claim your listing. Please try registering a new account.' );	
			}
			
		} else {
			
			//normal registration
			
			//Check other fields

			if ( empty($type) ) {
				$errors['empty_type'] = self::__( 'Please select an registration type.' );
			}
			if ( empty($company) ) {
				$errors['empty_company'] = self::__( 'You must enter a company name.' );
			}
			if ( empty($phone) ) {
				$errors['empty_phone'] = self::__( 'You must enter a phone number.' );
			}
			/*
			if ( empty($website) ) {
				$errors['empty_website'] = self::__( 'You must enter a website.' );
			}
			*/
			
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			
			//Create WP user
			$user_id = self::create_wp_user( $sanitized_user_login, $email_address, $password, $_POST );
			if ( $user_id ) {
				$user = wp_signon(
					array(
						'user_login' => $sanitized_user_login,
						'user_password' => $password,
						'remember' => true
					), false );
					
				//Send email
				$email_replace_keys = array('user_email' => $user->user_email, 'site_name' => get_option('blogname'), 'site_url' => home_url() );
				$email_data = array(
					'to_email' => $user->user_email,
					'from_email' => self::$notification_from_email,
					'from_name' => self::$notification_from_name,
					'subject' => self::build_email_subject('registration', $email_replace_keys),
					'content' => self::build_email_content('registration', $email_replace_keys),
					'is_html' => self::$notification_format_is_html
				);
				
				$result = SF_FV::send_email($email_data);
				
				//Set success message
				self::set_message( self::__('Thank you for registering! You are now logged in to your account!'), self::MESSAGE_STATUS_SUCCESS );
					
				//Redirect newly registered logged in user
				if ( isset( $_REQUEST['redirect_to'] ) && !empty( $_REQUEST['redirect_to'] ) ) {
					$redirect = str_replace( home_url(), '', $_REQUEST['redirect_to'] ); // in case the home_url is already added
					$url = home_url( $redirect );
				} else {
					$url = self::user_profile_url( $user->ID );
				}
				wp_redirect( $url, 303 );
				exit();
				
			}
		}
	}
	
	public function create_wp_user( $username, $email_address, $password = '', $submitted = array() ) {
		$password = ( $password != '' ) ? $password: wp_generate_password( 12, false );
		$username = ( !empty( $username ) ) ? $username : $email_address;
		
		//Create the wordpress user
		$user_id = wp_create_user( $username, $password, $email_address );
		if ( !$user_id || is_wp_error( $user_id ) ) {
			self::set_message( self::__( 'Couldn&#8217;t register you... please contact the site administrator!' ) );
			return FALSE;
		}
		
		//Check if claiming listing
		if ( !empty($submitted['claim_listing']) ) {
			
			//Decode
			$claim_data = json_decode(base64_decode($submitted['claim_listing']), true);
			if ( isset($claim_data['type']) && isset($claim_data['listing_id']) ) {
				//Get claim data
				$claim_post_id = trim($claim_data['listing_id']);
				$claim_post_type = get_post_type( $claim_post_id );
				
				if ( $claim_post_type == SF_Facility::POST_TYPE && $claim_data['type'] == SF_Facility::POST_TYPE) {
					//Set user to existing listing
					$facility_id = $claim_post_id;
					update_user_meta( $user_id, self::USER_TYPE_META_KEY, self::USER_TYPE_FACILITY );
					update_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, $facility_id );
					
					//Update listing with user id
					SF_Facility::save_field($facility_id, 'user_id', $user_id);
					
				} elseif ( $claim_post_type == SF_Contractor::POST_TYPE && $claim_data['type'] == SF_Contractor::POST_TYPE ) {
					//Set user to existing listing
					$contractor_id = $claim_post_id;
					update_user_meta( $user_id, self::USER_TYPE_META_KEY, self::USER_TYPE_CONTRACTOR );
					update_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, $contractor_id );
					
					//Update listing with user id
					SF_Contractor::save_field($facility_id, 'user_id', $user_id);
					
				}
			}
			
		} else {
			
			//Regular new registration ( not claiming a listing )
		
			//Get other data
			$data = array('company' => stripslashes($submitted['company']), 'phone' => stripslashes($submitted['phone']), 'website' => stripslashes($submitted['website']) );
			$post_title = stripslashes($submitted['company']);
			
			// Create the account type
			if ( $submitted['type'] == self::USER_TYPE_FACILITY ) {
				
				$facility_id = SF_Facility::new_facility($user_id, $post_title, $data);
				update_user_meta( $user_id, self::USER_TYPE_META_KEY, self::USER_TYPE_FACILITY );
				update_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, $facility_id );
				
			} elseif ($submitted['type'] == self::USER_TYPE_CONTRACTOR) {
				
				$contractor_id = SF_Contractor::new_contractor($user_id, $post_title, $data);
				update_user_meta( $user_id, self::USER_TYPE_META_KEY, self::USER_TYPE_CONTRACTOR );
				update_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, $contractor_id );
			}
			
		}
		
		//wp_new_user_notification( $user_id ); //Notify admin
		do_action( 'new_user_created', $user_id, $_POST);
		return $user_id;
	}
	
	public static function is_registration_page() {
		return is_page_template( 'template-register.php' );
	}
	
	public function redirect_users_from_register_form() {
		
		// Registered users shouldn't be here. Send them elsewhere
		if ( self::is_registration_page() && is_user_logged_in() ) {
			self::set_message( 'You are already logged in', self::MESSAGE_STATUS_ERROR );
			wp_redirect( home_url(), 303 );
			exit();
		}
	}
	
	public static function register_url( $url ) {
		$url = home_url(self::REGISTER_PATH);
		return $url;
	}
	
	
	/* Profile Edit */
	
	public function load_profile_edit() {
		global $facility_id, $contractor_id, $fields, $user_fields;
		
		// If facility profile
		if ( self::is_facility_profile_edit_page() ) {
			if ( is_user_logged_in() ) {
				$facility_id = SF_Facility::get_facility_id_for_user();
				
				if ( $facility_id ) {
					$fields = SF_Facility::load_form_fields($facility_id);
					$user_fields = get_userdata( get_current_user_id() );
				} else {
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				$redirect_to = str_replace( home_url(), '', $_SERVER['REQUEST_URI'] ); // in case the home_url is already added
				$redirect = add_query_arg( array('redirect_to' => $redirect_to), home_url(self::LOGIN_PATH) );
				wp_redirect( $redirect );
				exit();
			}
			
		// If contractor profile
		} elseif ( self::is_contractor_profile_edit_page() ) {
			if ( is_user_logged_in() ) {
				$contractor_id = SF_Contractor::get_contractor_id_for_user();
				if ( $contractor_id ) {
					$fields = SF_Contractor::load_form_fields($contractor_id);
					$user_fields = get_userdata( get_current_user_id() );
				} else {
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				$redirect_to = str_replace( home_url(), '', $_SERVER['REQUEST_URI'] ); // in case the home_url is already added
				$redirect = add_query_arg( array('redirect_to' => $redirect_to), home_url(self::LOGIN_PATH) );
				wp_redirect( $redirect );
				exit();
			}
		}
		
		//Handle any profile form submissions
		self::handle_profile_edit();
		self::handle_profile_edit_upload_file();
	}
	
	public static function is_facility_profile_edit_page() {
		return is_page_template( 'template-facility-profile.php' );
	}
	public static function is_contractor_profile_edit_page() {
		return is_page_template( 'template-contractor-profile.php' );
	}
	
	public static function handle_profile_edit() {
		
		// Edit form Attempt
		if ( !empty( $_POST['fv_profile_edit'] ) && wp_verify_nonce( $_POST['fv_profile_edit_nonce'], 'fv_profile_edit_nonce' ) && is_user_logged_in() ) {
			
			//Get type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, self::USER_TYPE_META_KEY, true);
			
			if ( $_POST['fv_profile_edit'] == self::USER_TYPE_FACILITY && $_POST['fv_profile_edit'] == $user_type ) {
				$facility_id = get_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, true);
				self::handle_facility_profile_edit($user_id, $facility_id);
				
			} elseif ( $_POST['fv_profile_edit'] == self::USER_TYPE_CONTRACTOR && $_POST['fv_profile_edit'] == $user_type ) {
				$contractor_id = get_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, true);
				self::handle_contractor_profile_edit($user_id, $contractor_id);
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		}
	}
	
	public static function handle_profile_edit_upload_file() {
		//Upload files
		if ( !empty( $_POST['fv_profile_edit_upload_file'] ) && wp_verify_nonce( $_POST['fv_profile_edit_upload_file_nonce'], 'fv_profile_edit_upload_file_nonce' ) && is_user_logged_in() ) {
			
			//Get type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, self::USER_TYPE_META_KEY, true);
			
			if ( $_POST['fv_profile_edit_upload_file'] == self::USER_TYPE_FACILITY && $_POST['fv_profile_edit_upload_file'] == $user_type ) {
				$facility_id = get_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, true);
				if ( isset($_FILES['upload_file']) ) {
					$label = ($_POST['upload_file_label']) ? $_POST['upload_file_label'] : '';
					$set_as_featured = ( $_POST['upload_type'] == 'featured'  ) ? true : false;
					$result = SF_Facility::save_attachment( $facility_id, 'upload_file', array('post_content' => $label), $set_as_featured );
					if ( $result['result'] == TRUE ) {
						self::set_message( 'Your file has been successfully uploaded.', self::MESSAGE_STATUS_SUCCESS );	
					} else {
						self::set_message( $result['error'], self::MESSAGE_STATUS_ERROR );	
					}
				}
				
			} elseif ( $_POST['fv_profile_edit_upload_file'] == self::USER_TYPE_CONTRACTOR && $_POST['fv_profile_edit_upload_file'] == $user_type ) {
				$contractor_id = get_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, true);
				if ( isset($_FILES['upload_file']) ) {
					$label = ($_POST['upload_file_label']) ? $_POST['upload_file_label'] : '';
					$set_as_featured = ( $_POST['upload_type'] == 'featured'  ) ? true : false;
					$result = SF_Contractor::save_attachment( $contractor_id, 'upload_file', array('post_content' => $label), $set_as_featured );
					if ( $result['result'] == TRUE ) {
						self::set_message( 'Your file has been successfully uploaded.', self::MESSAGE_STATUS_SUCCESS );	
					} else {
						self::set_message( $result['error'], self::MESSAGE_STATUS_ERROR );	
					}
				} 
				
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		}
		//Modify files
		if ( !empty( $_POST['fv_profile_edit_modify_file'] ) && wp_verify_nonce( $_POST['fv_profile_edit_modify_file_nonce'], 'fv_profile_edit_modify_file_nonce' ) && is_user_logged_in() ) {
			
			//Get user type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, self::USER_TYPE_META_KEY, true);
			$user_type_id = get_user_meta( $user_id, self::USER_TYPE_ID_META_KEY, true);
			
			if ( $_POST['fv_profile_edit_modify_file'] == self::USER_TYPE_FACILITY && $_POST['fv_profile_edit_modify_file'] == $user_type ) {
				$valid_user_type = true;
				
			} elseif ( $_POST['fv_profile_edit_modify_file'] == self::USER_TYPE_CONTRACTOR && $_POST['fv_profile_edit_modify_file'] == $user_type ) {
				$valid_user_type = true;
				
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
			
			//perform action
			if ( $valid_user_type ) {
				
				if ( isset($_POST['upload_action']) && isset($_POST['upload_attachment_id']) ) {
					
					if ($_POST['upload_action'] == 'edit' ) {
						$label = ($_POST['upload_file_label']) ? $_POST['upload_file_label'] : '';
						//Update post for attachment
						global $wpdb;
						$result = $wpdb->update( $wpdb->posts, array('post_content' => stripslashes($label)), array( 'ID' => $_POST['upload_attachment_id'] ) );
						if ( $result == FALSE ) {
							self::set_message( 'Your file could not be edited.', self::MESSAGE_STATUS_ERROR );	
						} else {
							self::set_message( 'Your file has been successfully edited.', self::MESSAGE_STATUS_SUCCESS );	
						}
						
					} elseif ($_POST['upload_action'] == 'delete' ) {
						$result = wp_delete_attachment( $_POST['upload_attachment_id'] );
						if ( $result == FALSE ) {
							self::set_message( 'Your file could not be deleted.', self::MESSAGE_STATUS_ERROR );	
						} else {
							if ( $_POST['upload_type'] == 'featured' ) {
								update_post_meta( $user_type_id, '_thumbnail_id', false );	
							}
							self::set_message( 'Your file has been successfully deleted.', self::MESSAGE_STATUS_SUCCESS );	
						}
					}
					
				}
			}
		}
		
	}
	
	public static function handle_facility_profile_edit($user_id, $facility_id) {
		$errors = array();
		
		//Get the user
		$user = get_user_by('id', $user_id);
		$user_email_address = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		
		$password = isset( $_POST['password'] ) ? $_POST['password']:'';
		$password2 = isset( $_POST['confirm-password'] ) ? $_POST['confirm-password'] : ''; //same thing

		$display_name = isset( $_POST['display_name'] ) ? $_POST['display_name'] : '';
		
		$user_email_address = trim($user_email_address);

		// check Password (if changing)
		if ( $password != '' || $password2 != '' ) {
			if ( $password != $password2 ) {
				$errors['password_mismatch'] = self:: __( 'Passwords did not match.' );
			}
		}

		// Check the e-mail address ( if changing )
		if ( $user_email_address && $user->user_email != $user_email_address ) {
			if ( $user_email_address == '' ) {
				$errors['empty_email'] = self::__( 'Please type your e-mail address.' );
			} elseif ( ! is_email( $user_email_address ) ) {
				$errors['invalid_email'] = self:: __( 'The email address isn&#8217;t correct.' );
				$email_address = '';
			} elseif ( email_exists( $user_email_address ) ) {
				$errors['email_exists'] = self::__( 'This email is already registered, please choose another one.' );
			}
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
		
			//Update WP user
			$update_wp_user = array();
			if ( ($user_email_address && $user->user_email != $user_email_address) || ($password && $password2) ) {
				
				$update_wp_user['ID'] = $user_id;
				if ( $user_email_address && $user->user_email != $user_email_address ) {
					$update_wp_user['user_email'] = $user_email_address;
				}
				if ( $password && $password2 ) {
					$up_wp_user['user_pass'] = $password;
				}
				if ( $display_name ) {
					$up_wp_user['display_name'] == $display_name;
				}
				$wp_user_result = wp_update_user( $update_wp_user ) ;
				if ( is_wp_error($wp_user_result) ) {
					self::set_message( $wp_user_result->get_error_message(), self::MESSAGE_STATUS_ERROR );	
				}
			}
			
			//Save fields
			SF_Facility::save_form_fields($facility_id);
			
			self::set_message( 'Your profile has been saved.', self::MESSAGE_STATUS_SUCCESS );	
			
			return TRUE;
		}
	}
	
	public static function handle_contractor_profile_edit($user_id, $contractor_id) {
		$errors = array();
		
		//Get the user
		$user = get_user_by('id', $user_id);
		$user_email_address = isset( $_POST['user_email'] ) ? $_POST['user_email'] : '';
		
		$password = isset( $_POST['password'] ) ? $_POST['password']:'';
		$password2 = isset( $_POST['confirm-password'] ) ? $_POST['confirm-password'] : ''; //same thing

		$display_name = isset( $_POST['display_name'] ) ? $_POST['display_name'] : '';
		
		$user_email_address = trim($user_email_address);

		// check Password (if changing)
		if ( $password != '' || $password2 != '' ) {
			if ( $password != $password2 ) {
				$errors['password_mismatch'] = self:: __( 'Passwords did not match.' );
			}
		}

		// Check the e-mail address ( if changing )
		if ( $user_email_address && $user->user_email != $user_email_address ) {
			if ( $user_email_address == '' ) {
				$errors['empty_email'] = self::__( 'Please type your e-mail address.' );
			} elseif ( ! is_email( $user_email_address ) ) {
				$errors['invalid_email'] = self:: __( 'The email address isn&#8217;t correct.' );
				$email_address = '';
			} elseif ( email_exists( $user_email_address ) ) {
				$errors['email_exists'] = self::__( 'This email is already registered, please choose another one.' );
			}
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
		
			//Update WP user
			$update_wp_user = array();
			if ( ($user_email_address && $user->user_email != $user_email_address) || ($password && $password2) ) {
				
				$update_wp_user['ID'] = $user_id;
				if ( $user_email_address && $user->user_email != $user_email_address ) {
					$update_wp_user['user_email'] = $user_email_address;
				}
				if ( $password && $password2 ) {
					$up_wp_user['user_pass'] = $password;
				}
				if ( $display_name ) {
					$up_wp_user['display_name'] == $display_name;
				}
				$wp_user_result = wp_update_user( $update_wp_user ) ;
				if ( is_wp_error($wp_user_result) ) {
					self::set_message( $wp_user_result->get_error_message(), self::MESSAGE_STATUS_ERROR );	
				}
			}
			
			//Save fields
			SF_Contractor::save_form_fields($contractor_id);
			
			self::set_message( 'Your profile has been saved.', self::MESSAGE_STATUS_SUCCESS );	
			
			return TRUE;
		}
	}
	
	// Subscriptions for Members (Paypal buttons)
	public static function get_new_facility_subscription_form($user_id, $facility_id, $button = '', $membership_type = 'F1') {
		
		$invoice = $user_id.'-F'.$facility_id.'-M'.$membership_type.'-'.time();
		$code = $user_id.'-F'.$facility_id.'-M'.$membership_type.'-'.time();
		
		$item_name = self::$facility_membership_types[$membership_type]['label']; 
		$amount = self::$facility_membership_types[$membership_type]['cost'];
		
		$view = self::get_new_subscription_form($invoice, $amount, $code, $item_name, $button);
		return $view;
	}
	
	public static function get_new_contractor_subscription_form($user_id, $contractor_id, $button = '', $membership_type = 'C1') {
		
		$invoice = $user_id.'-C'.$contractor_id.'-M'.$membership_type.'-'.time();
		$code = $user_id.'-C'.$contractor_id.'-M'.$membership_type.'-'.time();
		
		$item_name = self::$contractor_membership_types[$membership_type]['label']; 
		$amount = self::$contractor_membership_types[$membership_type]['cost'];
		
		$view = self::get_new_subscription_form($invoice, $amount, $code, $item_name, $button);
		return $view;
	}
	
	public static function get_new_subscription_form($invoice, $amount, $code, $item_name, $button = '') {
		//Usage notes: http://www.paypalobjects.com/en_US/ebook/subscriptions/html.html
		
		if ( empty($button) ) {
			$button = '<input class="paypal_subscribe_btn" type="image" src="http://www.paypal.com/en_US/i/btn/btn_subscribe_LG.gif" border="0" name="submit" alt="Setup subscription at PayPal">';	
		}
		
		//Get button
		$view = ob_start();
		?>
        <form name="_xclick" action="<?php echo self::$url_paypal_api_endpoint; ?>" method="post">
            <input type="hidden" name="cmd" value="_xclick-subscriptions">
            <input type="hidden" name="item_name" value="<?php echo $item_name; ?>">
            <input type="hidden" name="business" value="<?php echo get_option('fv_paypal_id'); ?>">
            <input type="hidden" name="invoice" value="<?php echo $invoice; ?>">
            <input type="hidden" name="custom" value="<?php echo $code; ?>">
            <input type="hidden" name="return" value="<?php echo self::$url_paypal_return; ?>">
            <input type="hidden" name="rm" value="2">
            <input type="hidden" name="cancel_return" value="<?php echo self::$url_paypal_cancel; ?>">
            <input type="hidden" name="currency_code" value="USD">
            <input type="hidden" name="no_shipping" value="1">
            <input type="hidden" name="no_note" value="1">
            <input type="hidden" name="modify" value="0">
            <?php echo $button ; ?>
            <input type="hidden" name="a3" value="<?php echo number_format($amount, 2, '.', ''); ?>">
            <input type="hidden" name="p3" value="1">
            <input type="hidden" name="t3" value="M">
            <input type="hidden" name="src" value="1">
            <input type="hidden" name="sra" value="1">
        </form>
        <?php
		$view = ob_get_clean();
		return $view;
	}
	
	
	public static function handle_ipn_calls() {
		
		if ( isset( $_GET['fv_ipn_listener'] ) ) {
			if (self::DEBUG) error_log('fv_ipn_listener - '.$_SERVER['REQUEST_URI'].' - POST: '.print_r($_POST, true));
			//handle membership setup & renewal payment
			if ( $_POST['txn_type'] == 'subscr_signup' || $_POST['txn_type'] == 'subscr_payment' ) {
				$code = self::parse_membership_invoice_code ( $_POST['custom'] );
				if ( $code ) {
					$result = self::setup_membership($code, $_POST);
				}
			} elseif ( $_POST['txn_type'] == 'subscr_cancel' || $_POST['txn_type'] == 'subscr_eot' || $_POST['txn_type'] == 'subscr_failed' ) {
				//handle membership cancellation
				$code = self::parse_membership_invoice_code ( $_POST['custom'] );
				if ( $code ) {
					$result = self::cancel_membership($code, $_POST);
					
				}
			}
		}
		if ( isset( $_GET['fv_pay_return'] ) ) {
			if (self::DEBUG) error_log('fv_pay_return - '.$_SERVER['REQUEST_URI'].' - POST: '.print_r($_POST, true));
			//handle membership setup
			if ( $_POST['txn_type'] == 'subscr_signup' ) {
				$code = self::parse_membership_invoice_code ( $_POST['custom'] );
				if ( $code ) {
					$result = self::setup_membership($code, $_POST);
					//Show result message
					if ( $result ) {
						self::set_message( self::__('Success! Thank you for your subscription.'), self::MESSAGE_STATUS_SUCCESS );
					} else {
						//no message
					}
					wp_redirect( add_query_arg( array( 'message' => 'signup_success' ), SF_Users::user_profile_url( get_current_user_id() )));
					exit();
				}
			}
		}
		if ( isset( $_GET['fv_pay_cancel'] ) ) {
			//payment form cancelled before completion
			if (self::DEBUG) error_log('fv_pay_cancel - '.$_SERVER['REQUEST_URI'].' - POST: '.print_r($_POST, true));
			//Show message to user
			self::set_message( self::__('The signup process was cancelled.'), self::MESSAGE_STATUS_ERROR );
			wp_redirect( add_query_arg( array( 'message' => 'signup_cancelled' ), SF_Users::user_profile_url( get_current_user_id() )));
			exit();
		}
	}
	
	public static function setup_membership($code, $ipn) {
		
		if ( $code['user_id'] && $code['membership_type'] ) {
			
			if ( !empty($code['contractor_id']) ) {
				
				//Check if already processed 
				$transaction_history = SF_Contractor::get_field_multiple($code['contractor_id'], 'membership_history');
				if ( !empty($transaction_history) ) {
					foreach ( $transaction_history as $history ) {
						if ( $history['invoice'] == $ipn['invoice'] ) {
							//If is a renewal payment?
							if ( $ipn['txn_type'] == 'subscr_payment' )  {
								//TXN is Renewal or initial payment, so allow it to continue
								
							} else {
								//found in history
								if (self::DEBUG) error_log('skipping setup_membership - NEUTRAL - already setup: '.$membership_type.' - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));
								return TRUE; //Dont go any further
							}
						}
					}
				}
				
				//Validate membership ( check if matches a local membership type )
				$membership_type = $code['membership_type'];
				if ( !isset(self::$contractor_membership_types[$membership_type]['type']) ) {
					if (self::DEBUG) error_log('setup_membership - FAIL - invalid membership type: '.$membership_type.' - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));
					return; //Dont go any further
				}
				
				//Valid
				$membership_type = $code['membership_type'];
				$item_name = self::$contractor_membership_types[$membership_type]['label']; 
				$item_amount = self::$contractor_membership_types[$membership_type]['cost'];
				$item_type = self::$contractor_membership_types[$membership_type]['type'];
				
				//Membership expires
				$item_expiration = strtotime('+1 year');
				
				//Prepare data log
				$save_data = array('txn_type' => $ipn['txn_type'], 'invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'code' => $code, 'ipn' => $ipn, 'date' => time());
				
				//Save based on type of item
				if ( $item_type == 'main' ) {
					
					//Main item type
					SF_Contractor::save_field($code['contractor_id'], 'membership_type', $code['membership_type']);
					SF_Contractor::save_field($code['contractor_id'], 'membership_expiration', $item_expiration);
					SF_Contractor::save_field($code['contractor_id'], 'membership_data', $save_data);	//Save data
					
				} elseif ( $item_type == 'addon' ) {
					
					//Addon item type
					$addon_data = array('invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'expiration' => $item_expiration, 'addon' => $membership_type, 'date' => time());
					//Loop existing addons
					$addon_data_list = SF_Contractor::get_field_multiple($code['contractor_id'], 'membership_addon_data');
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							if ( $addon_list['invoice'] == $ipn['invoice'] ) { //already exists
								//Remove from addon list
								unset($addon_data_list[$addon_key]);
							}
						}
					}
					
					//Re save - overwrite all existing with new
					$addon_data_list[] = $addon_data; //add this addon to array
					SF_Contractor::delete_field($code['contractor_id'], 'membership_addon_data'); //delete all addon data fields
					//Save each again
					foreach ( $addon_data_list as $addon_key => $addon_list ) {
						SF_Contractor::save_field_multiple($code['contractor_id'], 'membership_addon_data', $addon_list);
					}
				}
				
				//Save transaction history
				SF_Contractor::save_field_multiple($code['contractor_id'], 'membership_history', $save_data); //Also save to history log
				
				return TRUE;
				
			} elseif ( !empty($code['facility_id']) ) {
				
				//Check if already processed 
				$transaction_history = SF_Facility::get_field_multiple($code['facility_id'], 'membership_history');
				if ( !empty($transaction_history) ) {
					foreach ( $transaction_history as $history ) {
						if ( $history['invoice'] == $ipn['invoice'] ) {
							//If is a renewal payment?
							if ( $ipn['txn_type'] == 'subscr_payment' )  {
								// TXN is Renewal or initial payment, so allow it to continue
								
							} else {
								//found in history
								if (self::DEBUG) error_log('skipping setup_membership - NEUTRAL - already setup: '.$membership_type.' - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));
								return; //Dont go any further
							}
						}
					}
				}
				
				//Validate membership ( check if matches a local membership type )
				$membership_type = $code['membership_type'];
				if ( !isset(self::$facility_membership_types[$membership_type]['type']) ) {
					if (self::DEBUG) error_log('setup_membership - FAIL - invalid membership type: '.$membership_type.' - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));
					return; //Dont go any further
				}
				
				//Valid
				$item_name = self::$facility_membership_types[$membership_type]['label']; 
				$item_amount = self::$facility_membership_types[$membership_type]['cost'];
				$item_type = self::$facility_membership_types[$membership_type]['type'];
				
				//Membership expires
				$item_expiration = strtotime('+1 year');
				
				//Prepare data log
				$save_data = array('txn_type' => $ipn['txn_type'], 'invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'code' => $code, 'ipn' => $ipn, 'date' => time());
				
				//Save based on type of item
				if ( $item_type == 'main' ) {
					
					//Main item type
					SF_Facility::save_field($code['facility_id'], 'membership_type', $code['membership_type']);
					SF_Facility::save_field($code['facility_id'], 'membership_expiration', $item_expiration);
					SF_Facility::save_field($code['facility_id'], 'membership_data', $save_data);	//Save data
					
				} elseif ( $item_type == 'addon' ) {
					
					//Addon item type
					$addon_data = array('invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'expiration' => $item_expiration, 'addon' => $membership_type, 'date' => time());
					//Loop existing addons
					$addon_data_list = SF_Facility::get_field_multiple($code['facility_id'], 'membership_addon_data');
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							if ( $addon_list['invoice'] == $ipn['invoice'] ) { //already exists
								//Remove from addon list
								unset($addon_data_list[$addon_key]);
							}
						}
					}
					
					//Re save - overwrite all existing with new
					$addon_data_list[] = $addon_data; //add this addon to array
					SF_Facility::delete_field($code['facility_id'], 'membership_addon_data'); //delete all addon data fields
					//Save each again
					foreach ( $addon_data_list as $addon_key => $addon_list ) {
						SF_Facility::save_field_multiple($code['facility_id'], 'membership_addon_data', $addon_list);
					}
			
				}
				
				//Save transaction history
				SF_Facility::save_field_multiple($code['facility_id'], 'membership_history', $save_data); //Also save to history log
				
				return TRUE;
				
			} else {
				if (self::DEBUG) error_log('setup_membership - FAIL - missing facility or contractor id - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));		
			}
			
		} else {
			if (self::DEBUG) error_log('setup_membership - FAIL - missing code parts - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));	
		}
		
		return FALSE;
	}
	
	public static function cancel_membership($code, $ipn) {
		
		if ( $code['user_id'] && $code['membership_type'] ) {
			
			if ( !empty($code['contractor_id']) ) {
				
				//Valid cancel call
				$membership_type = $code['membership_type'];
				$item_type = self::$contractor_membership_types[$membership_type]['type'];
				
				//Prepare data log
				$save_data = array('txn_type' => $ipn['txn_type'], 'invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'code' => $code, 'ipn' => $ipn, 'date' => time());
				
				//Save based on type of item
				if ( $item_type == 'main' ) {
					
					//Set as expired
					SF_Contractor::save_field($code['contractor_id'], 'membership_expiration', time()); //Expire now
					
				} elseif ( $item_type == 'addon' ) {
					
					//Loop existing data
					$addon_data_list = SF_Contractor::get_field_multiple($code['contractor_id'], 'membership_addon_data');
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							if ( $addon_list['invoice'] == $ipn['invoice'] ) {
								//Remove from addon list
								unset($addon_data_list[$addon_key]);
							}
						}
					}
					
					//Re save - overwrite all existing with new
					SF_Contractor::delete_field($code['contractor_id'], 'membership_addon_data'); //delete all addon data fields
					//Save each again
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							SF_Contractor::save_field_multiple($code['contractor_id'], 'membership_addon_data', $addon_list);
						}
					}
				}
				
				//Save transaction history
				SF_Contractor::save_field_multiple($code['contractor_id'], 'membership_history', $save_data); //Also save to history log
				
				return TRUE;
				
			} elseif ( !empty($code['facility_id']) ) {
				
				//Valid cancel call
				$membership_type = $code['membership_type'];
				$item_type = self::$facility_membership_types[$membership_type]['type'];
				
				//Prepare data log
				$save_data = array('txn_type' => $ipn['txn_type'], 'invoice' => $ipn['invoice'], 'code_string' => $ipn['custom'], 'code' => $code, 'ipn' => $ipn, 'date' => time());
				
				//Save based on type of item
				if ( $item_type == 'main' ) {
					
					//Set as expired
					SF_Facility::save_field($code['facility_id'], 'membership_expiration', time()); //Expire now
					
				} elseif ( $item_type == 'addon' ) {
					
					//Loop existing data
					$addon_data_list = SF_Facility::get_field_multiple($code['facility_id'], 'membership_addon_data');
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							if ( $addon_list['invoice'] == $ipn['invoice'] ) {
								//Remove from addon list
								unset($addon_data_list[$addon_key]);
							}
						}
					}
					
					//Re save - overwrite all existing with new
					SF_Facility::delete_field($code['facility_id'], 'membership_addon_data'); //delete all addon data fields
					//Save each again
					if ( !empty($addon_data_list) ) {
						foreach ( $addon_data_list as $addon_key => $addon_list ) {
							SF_Facility::save_field_multiple($code['facility_id'], 'membership_addon_data', $addon_list);
						}
					}
					
				}
				
				//Save transaction history
				SF_Facility::save_field_multiple($code['facility_id'], 'membership_history', $save_data); //Also save to history log
				
				return TRUE;
				
			} else {
				if (self::DEBUG) error_log('cancel_membership - FAIL - missing facility or contractor id - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));		
			}
			
		} else {
			if (self::DEBUG) error_log('cancel_membership - FAIL - missing code parts - code: '.print_r($code, true).' - IPN: '.print_r($ipn, true));	
		}
		return FALSE;
	}
	
	private static function parse_membership_invoice_code($code_string) {
		$array = explode('-', $code_string);
		$parsed = false;
		if ( is_array($array) ) {
			//Get user id (first array part)
			$parsed['user_id'] = (int)$array[0];
			//Get facility or contractor ID (2nd array part)
			if ( stripos($array[1], 'F') !== false && stripos($array[1], 'F') == 0 ) {
				$parsed['facility_id'] = (int)ltrim(strtoupper($array[1]), 'F');
			} elseif ( stripos($array[1], 'C') !== false && stripos($array[1], 'C') == 0 ) {
				$parsed['contractor_id'] = (int)ltrim(strtoupper($array[1]), 'C');
			}
			//Get Membership type id (3rd)
			if ( stripos($array[2], 'M') !== false && stripos($array[2], 'M') == 0 ) {
				$parsed['membership_type'] = (string)ltrim(strtoupper($array[2]), 'M');
			}
			$parsed['timestamp'] = (int)$array[3];
			$parsed['code_string'] = $code_string; //store complete original string code
		}
		return $parsed;
	}
	
	
	/*
	 * Singleton Design Pattern
	 * ------------------------------------------------------------- */
	private function __clone() {
		// cannot be cloned
		trigger_error( __CLASS__.' may not be cloned', E_USER_ERROR );
	}
	private function __sleep() {
		// cannot be serialized
		trigger_error( __CLASS__.' may not be serialized', E_USER_ERROR );
	}
	public static function get_instance() {
		if ( !( self::$instance && is_a( self::$instance, __CLASS__ ) ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		
	}
}
