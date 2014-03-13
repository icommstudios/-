<?php

class SF_Endorsements extends SF_FV {
	
	const DEBUG = FALSE;
	
	private static $meta_keys = array(
		'rating' => '_rating', // string
		'related_project_id' => '_related_project_id', //string
	);
	
	public static function init() {
		
		// Changes to built-in Comments fields
		add_action( 'comment_post', array(get_class(), 'save_comment_fields'), 99, 1);
		
		// Redirect
		add_filter( 'comment_post_redirect', array(get_class(), 'redirect_after_comment') );


	}
	
	
	public function redirect_after_comment($location) {
		$redirect_url = SF_Users::user_profile_url();
		return $redirect_url;
	}
	
	public function save_comment_fields($comment_id) {
		
		//Save custom form fields
		if ( isset( $_POST[self::$meta_keys['rating']] ) ) {
			update_comment_meta($comment_id, self::$meta_keys['rating'], stripslashes($_POST[self::$meta_keys['rating']]) );
		}
		if ( isset( $_POST[self::$meta_keys['related_project_id']] ) ) {
			update_comment_meta($comment_id, self::$meta_keys['related_project_id'], stripslashes($_POST[self::$meta_keys['related_project_id']]) );
			
			//Record endorsement to project
			$comment_project_id = stripslashes($_POST[self::$meta_keys['related_project_id']]);
			$user_type_data = fv_get_current_user_type_id();
			if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_FACILITY ) {
				update_post_meta($comment_project_id, '_endorsement_id_by_facility', $comment_id);
			} elseif ( $user_type_data['user_type'] == SF_Users::USER_TYPE_CONTRACTOR ) {
				update_post_meta($comment_project_id, '_endorsement_id_by_contractor', $comment_id);
			}
		}
		
		//Display message
		if ( !is_admin() && isset( $_POST[self::$meta_keys['rating']] ) ) {
			//Did we just save an endorsement
			self::set_message( self::__('Thank you for completing the Endorsement form!'), self::MESSAGE_STATUS_SUCCESS );
		}
	}
	
	public static function get_field( $comment_id, $meta_key = '' ) {
		$value = '';
		if ($comment_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = get_post_meta( $comment_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $comment_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($comment_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = update_post_meta( $comment_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}

}
