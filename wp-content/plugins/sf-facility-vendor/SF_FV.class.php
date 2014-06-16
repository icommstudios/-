<?php

/**
 * Base class
 */

abstract class SF_FV {
	
	const DEBUG = TRUE; //Set to FALSE in production environment
	
	const FV_VERSION = '1.0'; //Current version of this plugin - Matches the plugin version.
	const PLUGIN_NAME = 'Facility Vendor'; //Plugin Name
	const TEXT_DOMAIN = 'facility-vendor'; //Plugin's text-domain
	
	const MESSAGE_STATUS_INFO = 'info';
	const MESSAGE_STATUS_ERROR = 'danger';
	const MESSAGE_STATUS_SUCCESS = 'success';
	const MESSAGE_STATUS_WARNING = 'warning';
	
	const MESSAGE_META_KEY = 'sf_fv_messages';
	
	private static $messages = array();
	
	static $notification_format_is_html = false;
	static $notification_from_email;
	static $notification_from_name;
	static $notification_email_subject_key = array(
		'reset_password' => "fv_email_subject_reset_password", //wordpess option key
		'registration' => "fv_email_subject_registration",
		'message_notification' => "fv_email_subject_message_notification",
		'welcome_invite' => "fv_email_subject_welcome_invite",
	);
	static $notification_email_content_key = array(
		'reset_password' => "fv_email_content_reset_password",
		'registration' => "fv_email_content_registration",
		'message_notification' => "fv_email_content_message_notification",
		'welcome_invite' => "fv_email_content_welcome_invite",
	);
	static $notification_email_replace_codes = array(
		'reset_password' => array('user_email', 'site_name', 'site_url', 'reset_link'), //replace codes usable on this 
		'registration' => array('user_email', 'site_name', 'site_url'),
		'message_notification' => array('user_email', 'site_name', 'site_url', 'message_title', 'message_link'),
		'welcome_invite' => array( 'user_email', 'name', 'site_name', 'site_url', 'promo_code', 'register_link_with_promo_code'),
	);
	
	
	static $countries = array(
		'US' => "United States",
		'CA' => "Canada",
	);
	
	static $us_states = array(
		'AL' => 'Alabama',
		'AK' => 'Alaska',
		'AS' => 'American Samoa',
		'AZ' => 'Arizona',
		'AR' => 'Arkansas',
		'AE' => 'Armed Forces - Europe',
		'AP' => 'Armed Forces - Pacific',
		'AA' => 'Armed Forces - USA/Canada',
		'CA' => 'California',
		'CO' => 'Colorado',
		'CT' => 'Connecticut',
		'DE' => 'Delaware',
		'DC' => 'District of Columbia',
		'FM' => 'Federated States of Micronesia',
		'FL' => 'Florida',
		'GA' => 'Georgia',
		'GU' => 'Guam',
		'HI' => 'Hawaii',
		'ID' => 'Idaho',
		'IL' => 'Illinois',
		'IN' => 'Indiana',
		'IA' => 'Iowa',
		'KS' => 'Kansas',
		'KY' => 'Kentucky',
		'LA' => 'Louisiana',
		'ME' => 'Maine',
		'MH' => 'Marshall Islands',
		'MD' => 'Maryland',
		'MA' => 'Massachusetts',
		'MI' => 'Michigan',
		'MN' => 'Minnesota',
		'MS' => 'Mississippi',
		'MO' => 'Missouri',
		'MT' => 'Montana',
		'NE' => 'Nebraska',
		'NV' => 'Nevada',
		'NH' => 'New Hampshire',
		'NJ' => 'New Jersey',
		'NM' => 'New Mexico',
		'NY' => 'New York',
		'NC' => 'North Carolina',
		'ND' => 'North Dakota',
		'OH' => 'Ohio',
		'OK' => 'Oklahoma',
		'OR' => 'Oregon',
		'PA' => 'Pennsylvania',
		'PR' => 'Puerto Rico',
		'RI' => 'Rhode Island',
		'SC' => 'South Carolina',
		'SD' => 'South Dakota',
		'TN' => 'Tennessee',
		'TX' => 'Texas',
		'UT' => 'Utah',
		'VT' => 'Vermont',
		'VI' => 'Virgin Islands',
		'VA' => 'Virginia',
		'WA' => 'Washington',
		'WV' => 'West Virginia',
		'WI' => 'Wisconsin',
		'WY' => 'Wyoming'
	);
	
	static $ca_states = array(
		'AB' => 'Alberta',
		'BC' => 'British Columbia',
		'MB' => 'Manitoba',
		'NB' => 'New Brunswick',
		'NF' => 'Newfoundland',
		'NT' => 'Northwest Territories',
		'NS' => 'Nova Scotia',
		'NU' => 'Nunavut',
		'ON' => 'Ontario',
		'PE' => 'Prince Edward Island',
		'QC' => 'Quebec',
		'SK' => 'Saskatchewan',
		'YT' => 'Yukon Territory',
	);
	
	public static function init() {
		
		self::$notification_from_email = get_option('admin_email');
		self::$notification_from_name = get_option('blogname');
		
		//Load messages
		add_action( 'init', array( get_class(), 'load_messages' ), 0, 0 );
		
		//lookup meta cache
		add_action( 'added_post_meta', array( __CLASS__, 'flush_cache_after_meta_update' ), 10, 3 );
		add_action( 'updated_post_meta', array( __CLASS__, 'flush_cache_after_meta_update' ), 10, 3 );
		add_action( 'deleted_post_meta', array( __CLASS__, 'flush_cache_after_meta_update' ), 10, 3 );
	}

	

	/**
	 * Wrap around Wordpress's translation string function __() and add the text domain
	 */
	public static function __( $string ) { 
		return __( apply_filters( 'fv_translate_'.sanitize_title( $string ), $string ), self::TEXT_DOMAIN ); 
	}

	/**
	 * Wrap around Wordpress's translation string function and add the text domain
	 */
	public static function _e( $string ) { 
		return _e( apply_filters( 'fv_translate_'.sanitize_title( $string ), $string ), self::TEXT_DOMAIN );
	}
	
	public static function get_state_options( $args = array() ) {
		$states = self::$us_states;
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$states = array( '' => $args['include_option_none'] ) + $states;
		}
		if ( isset( $args['include_canada'] ) && $args['include_canada'] ) {
			$states = $states + self::$ca_states;
		}
		return $states;
	}

	public static function get_country_options( $args = array() ) {
		$countries = self::$countries;
		if ( isset( $args['include_option_none'] ) && $args['include_option_none'] ) {
			$countries = array( '' => $args['include_option_none'] ) + $countries;
		}
		return $countries;
	}
	
	/* Meta functions */
	
	/**
	 * Lookup posts with matching meta field
	 */
	public static function lookup_by_meta( $post_type, $meta = array() ) {
		
		// see if we've cached the result
		if ( count( $meta ) == 1 && count($post_type) == 1 ) {
			$cache_key = 'fv_lookup_meta_'.$post_type.'_'.reset( array_keys( $meta ) );
			$cache_index = reset( array_values( $meta ) );
			$cache = wp_cache_get( $cache_key, 'sf_fv' );
			if ( is_array( $cache ) && isset( $cache[$cache_index] ) ) {
				return $cache[$cache_index];
			}
		}

		// Optionally bypass the standard db call
		$result = apply_filters( 'fv_lookup_meta', NULL, $post_type, $meta );

		if ( !is_array( $result ) ) {
			$args = array(
				'post_type' => $post_type,
				'post_status' => 'any',
				'posts_per_page' => -1,
				'fields' => 'ids',
				'fv_bypass_filter' => TRUE
			);

			if ( !empty( $meta ) ) {
				foreach ( $meta as $key => $value ) {
					$args['meta_query'][] = array(
						'key' => $key,
						'value' => $value,
					);
				}
			}

			$result = get_posts( $args );
		}

		if ( count( $meta ) == 1 && count($post_type) == 1) {
			$cache[$cache_index] = $result;
			wp_cache_set( $cache_key, $cache, 'sf_fv' );
		}

		return $result;
	}

	public function flush_cache_after_meta_update( $meta_id, $object_id, $meta_key ) {
		self::flush_lookup_meta_cache( $meta_key, get_post_type( $object_id ) );
	}

	private static function flush_lookup_meta_cache( $meta_key, $post_type ) {
		wp_cache_delete( 'fv_lookup_meta_'.$post_type.'_'.$meta_key, 'sf_fv' );
	}
	
	// Messages 
	public static function has_messages() {
		$msgs = self::get_messages();
		return !empty( $msgs );
	}

	public static function set_message( $message, $status = self::MESSAGE_STATUS_INFO ) {
		if ( !isset( self::$messages ) ) {
			self::load_messages();
		}
		$message = self::__( $message );
		if ( !isset( self::$messages[$status] ) ) {
			self::$messages[$status] = array();
		}
		self::$messages[$status][] = $message;
		self::save_messages();
	}

	public static function clear_messages() {
		self::$messages = array();
		self::save_messages();
	}

	private static function save_messages() {
		global $blog_id;
		$user_id = get_current_user_id();
		if ( !is_user_logged_in() ) {
			set_transient( 'fv_messaging_for_'.$_SERVER['REMOTE_ADDR'], self::$messages, 300 );
		}
		update_user_meta( $user_id, $blog_id.'_'.self::MESSAGE_META_KEY, self::$messages );
	}

	public static function get_messages( $type = NULL ) {
		if ( !isset( self::$messages ) ) {
			self::load_messages();
		}
		return self::$messages;
	}

	public static function load_messages() {
		$user_id = get_current_user_id();
		if ( !is_user_logged_in() ) {
			$messages = get_transient( 'fv_messaging_for_'.$_SERVER['REMOTE_ADDR'] );
		} else {
			global $blog_id;
			$messages = get_user_meta( $user_id, $blog_id.'_'.self::MESSAGE_META_KEY, TRUE );
		}
		if ( $messages ) {
			self::$messages = $messages;
		} else {
			self::$messages = array();
		}
		
	}

	public static function display_messages( $type = NULL ) {
		
		$type = ( isset( $_REQUEST['message_type'] ) ) ? $_REQUEST['message_type'] : $type ;
		$statuses = array();
		if ( $type == NULL ) {
			if ( isset( self::$messages[self::MESSAGE_STATUS_SUCCESS] ) ) {
				$statuses[] = self::MESSAGE_STATUS_SUCCESS;
			}
			if ( isset( self::$messages[self::MESSAGE_STATUS_INFO] ) ) {
				$statuses[] = self::MESSAGE_STATUS_INFO;
			}
			if ( isset( self::$messages[self::MESSAGE_STATUS_WARNING] ) ) {
				$statuses[] = self::MESSAGE_STATUS_WARNING;
			}
			if ( isset( self::$messages[self::MESSAGE_STATUS_ERROR] ) ) {
				$statuses[] = self::MESSAGE_STATUS_ERROR;
			}
		} elseif ( isset( self::$messages[$type] ) ) {
			$statuses = array( $type );
		}

		if ( !isset( self::$messages ) ) {
			self::load_messages();
		}
		$messages = array();
		
		echo '<div id="alert-wrapper" class="alert-wrapper col-sm-12" style="display: none;"><div class="container">';
		foreach ( $statuses as $status ) {
			foreach ( self::$messages[$status] as $message ) {
				?>
                <div class="alert alert-<?php echo $status; ?> alert-dismissable"><button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button><?php echo $message; ?></div>
                <?php
			}
			self::$messages[$status] = array();
		}
		echo '</div></div>';
		self::save_messages();
		if ( defined( 'DOING_AJAX' ) ) {
			exit();
		}
	}
	
	public static function send_email($email_data) {
		
		$from_email = ( strpos($email_data['from_email'], '@') !== false ) ? $email_data['from_email'] : self::$notification_from_email;
		$from_name = ( $email_data['from_name'] ) ? $email_data['from_name'] : self::$notification_from_name;
		
		$to_email = $email_data['to_email'];
		
		$subject = trim($email_data['subject']);
		$content = $email_data['content'];

		if ( $email_data['is_html'] ) {
			$headers = array(
				"From: ".$from_name." <".$from_email.">",
				"Content-Type: text/html"
			);
		} else {
			$headers = array(
				"From: ".$from_name." <".$from_email.">",
			);
		}
		$headers = implode( "\r\n", $headers ) . "\r\n";
		
		$result = wp_mail( $to_email, $subject, $content , $headers );
		return $result;
	}
	
	public static function build_email_content($email_option_key, $replace_data = array()) {
		$content = get_option(self::$notification_email_content_key[$email_option_key]);
		//Replace
		foreach ( $replace_data as $find => $replace ) {
			$content = str_replace("[".$find."]", $replace, $content);
		}
		return $content;
	}
	
	public static function build_email_subject($email_option_key, $replace_data = array()) {
		$content = get_option(self::$notification_email_subject_key[$email_option_key]);
		//Replace
		foreach ( $replace_data as $find => $replace ) {
			$content = str_replace("[".$find."]", $replace, $content);
		}
		return $content;
	}
	
}
