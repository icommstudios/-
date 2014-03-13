<?php

class SF_Message_Forms extends SF_FV {
	
	private static $instance;

	public static function init() {
		
		// Handle message form
		add_action( 'template_redirect', array( get_class(), 'handle_message_forms'), 11);
		add_action( 'template_redirect', array( get_class(), 'load_message_view'), 12);
		
	}
	
	// Message Forms
	public function handle_message_forms() {
		global $facility_id, $contractor_id, $message_fields;
		
		// If is send page
		if ( self::is_message_form_page() ) {
			
			if ( is_user_logged_in() ) {
				
				//Get type
				$user_id =  get_current_user_id();
				$user_type_data = fv_get_current_user_type_id($user_id);
				
				if ( $user_type_data['user_type_id'] ) {
					
					//Load any details
					if ( $user_type_data['user_type'] == SF_Users::USER_TYPE_FACILITY) {
						$facility_id = $user_type_data['user_type_id'];
					} elseif ( $user_type_data['user_type'] == SF_Users::USER_TYPE_CONTRACTOR) {
						$contractor_id = $user_type_data['user_type_id'];
					}
					
					//Handle any message forms
					self::handle_message_send($user_type_data);
								
				} else {
					self::set_message( self::__( 'Could not process your request. This user is not assigned to an Account.' ), self::MESSAGE_STATUS_ERROR );
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				self::set_message( self::__( 'Could not process your request. You must be logged in.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( home_url('/register') );	
				exit();
			}
	
		} 
		
		
	}
	
	public static function is_message_form_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['msg_form'] ) ) ? TRUE : FALSE;
	}
	
	public static function is_message_view_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['msg_view'] ) ) ? TRUE : FALSE;
	}
	
	
	
	public static function handle_message_send($user_type_data) {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_message_send'] ) && wp_verify_nonce( $_POST['fv_message_send_nonce'], 'fv_message_send_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			
			if ( !empty($user_type_data['user_type']) && !empty($user_type_data['user_type_id']) ) {
			
				$result = self::process_message_form($user_id, $user_type_data);
				
				//If success refresh page, if failed show page again
				if ( $result ) {
					$redirect = remove_query_arg('msg_form', $_SERVER['REQUEST_URI']);
					wp_redirect( $redirect );	
					exit();
				}
				//$redirect = add_query_arg(array('action' => 'messages'), SF_Users::user_profile_url());
				//wp_redirect( $redirect );	
				//exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function process_message_form($user_id, $user_type_data) {
		$errors = array();
		
		$to = isset( $_POST['to'] ) ? stripslashes($_POST['to']):'';
		$title = isset( $_POST['post_title'] ) ? stripslashes($_POST['post_title']):'';
		$content = isset( $_POST['post_content'] ) ? stripslashes($_POST['post_content']) : '';
		$type = isset( $_POST['type'] ) ? stripslashes($_POST['type']) : '';
		$related_project_id = isset( $_POST['related_project_id'] ) ? stripslashes($_POST['related_project_id']) : '';
		$reply_message_id = isset( $_POST['reply_message_id'] ) ? stripslashes($_POST['reply_message_id']) : '';
		$related_project_action = isset( $_POST['related_project_action'] ) ? stripslashes($_POST['related_project_action']) : '';
		
		if ( empty($type) ) {
			if ( !$to ) { //If normal message, then to is required
				$errors['empty_to'] = self:: __( 'Invalid message recipient.' );
			}
			if ( !$title ) { //If normal message, then title is required
				$errors['empty_message_name'] = self:: __( 'Please type a message title.' );
			}
			if ( !$content ) { //If normal message, then content is required
				$errors['empty_content'] = self:: __( 'Please type a message.' );
			}
		} elseif ( !empty($type) ) { //Not normal message
			if ( !$to ) { 
				$errors['empty_message'] = self:: __( 'Error occurred. Could not complete the action.' );
			}
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			
			// Valid form
			
			//Prepare message
			$message_id = false;
			$from_user_id = $user_id;
			$from_id = $user_type_data['user_type_id'];
			$to_id = trim($to);
			
			$message_data = array();
			$message_data['type'] = ( $type ) ? $type : 'message';
			$message_data['title'] = trim($title);
			$message_data['content'] = $content;
			if ( $related_project_id ) $message_data['related_project_id'] = $related_project_id;
			if ( $related_project_action ) $message_data['related_project_action'] = $related_project_action;
			
			// Handle any actions
			if ( $type == 'project_invite' ) {
				
				//Overrides, set message override values
				$message_data['title'] = 'Facility: '.get_the_title( $user_type_data['user_type_id'] ).' invites you to submit a proposal on the job: '.get_the_title($related_project_id);
				
				//Send
				$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
				self::set_message( 'Your invite message has been sent.', self::MESSAGE_STATUS_SUCCESS );
				
				//Save contractor as accepting invite to project invites
				$message_contractor_id = $to_id;
				$invite = array('contractor_id' => $message_contractor_id, 'date' => time());
				SF_Project::save_field_multiple($related_project_id, 'invited_contractors', json_encode($invite));
				
			} elseif ( $type == 'project_accept' ) {
				
				//Overrides, set message override values
				$message_data['title'] = 'Contractor: '.get_the_title( $user_type_data['user_type_id'] ).' accepted the invite to: '.get_the_title($related_project_id);
			
				//Continue
				if ( $reply_message_id ) {
					SF_Message::save_field($reply_message_id, 'related_project_action', 'project_accept'); //Mark original message as accepted
				}
				
				//Send
				$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
				self::set_message( 'Your accept message has been sent.', self::MESSAGE_STATUS_SUCCESS );
				
				//Save contractor as accepting invite to project invites
				$message_contractor_id = $user_type_data['user_type_id'];
				$invite_accepted = array('contractor_id' => $message_contractor_id, 'date' => time());
				SF_Project::save_field_multiple($related_project_id, 'invited_contractors_accepted', json_encode($invite_accepted));
				
			} elseif ( $type == 'project_decline' ) {
				
				//Overrides, set message override values
				$message_data['title'] = 'Contractor: '.get_the_title( $user_type_data['user_type_id'] ).' declined job: '.get_the_title($related_project_id);
				
				//Mark as decline
				if ( $reply_message_id ) {
					SF_Message::save_field($reply_message_id, 'related_project_action', 'project_decline'); //Mark original message as declined
				}
				
				//Send
				$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
				self::set_message( 'Your decline message has been sent.', self::MESSAGE_STATUS_SUCCESS );
				
			} else {
				//All other types of messages
				
				//Send
				$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
				self::set_message( 'Your message has been sent.', self::MESSAGE_STATUS_SUCCESS );
			}
			
			return TRUE;
		}
	}
	
	// Message View
	public function load_message_view() {
		global $facility_id, $contractor_id, $message_fields;
		
		// If is view page
		if ( self::is_message_view_page() ) {
			
			if ( is_user_logged_in() ) {
				
				//Get type
				$user_id =  get_current_user_id();
				$user_type_data = fv_get_current_user_type_id($user_id);
				
				if ( $user_type_data['user_type_id'] ) {
					// Load any details
					$message_fields = SF_Message::load_form_fields( $_GET['msg_view'] );
					
					//Is user allowed to view message
					if ( $message_fields['to_id'] == $user_type_data['user_type_id'] || $message_fields['from_id'] == $user_type_data['user_type_id'] ) {
						//Allowed user (is either to or from id)
						
					} else {
						self::set_message( self::__( 'Could not process your request. This user is not authorized to view this message.' ), self::MESSAGE_STATUS_ERROR );
						wp_redirect( home_url() );	
						exit();
					}
					
				} else {
					self::set_message( self::__( 'Could not process your request. This user is not assigned to an Account.' ), self::MESSAGE_STATUS_ERROR );
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				self::set_message( self::__( 'Could not process your request. You must be logged in.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( home_url('/register') );	
				exit();
			}
	
		} 
		
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
