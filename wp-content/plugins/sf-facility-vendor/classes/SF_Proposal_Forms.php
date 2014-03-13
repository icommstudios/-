<?php

class SF_Proposal_Forms extends SF_FV {
	
	private static $instance;

	public static function init() {
		
		// Proposal view
		add_action( 'template_redirect', array( get_class(), 'load_proposal_view'), 9);
		
		// Proposal form
		add_action( 'template_redirect', array( get_class(), 'load_proposal_edit'), 11);
		add_action( 'template_redirect', array( get_class(), 'load_proposal_accept_form'), 11);

		
	}
	
	// Proposal form 
	public function load_proposal_edit() {
		global $contractor_id, $proposal_fields;
		
		// If is proposal send form
		if ( self::is_proposal_edit_page() ) {
			
			if ( is_user_logged_in() ) {
				$contractor_id = SF_Contractor::get_contractor_id_for_user();
				
				if ( $contractor_id ) {
					
					if ( $_GET['prop_edit'] ) { //existing proposal id
						$proposal_fields = SF_Proposal::load_form_fields( $_GET['prop_edit'] );
					} else {
						$proposal_fields = SF_Proposal::load_form_fields();
					}
					
				} else {
					self::set_message( self::__( 'Could not process your request. This user is not assigned to a Contractor.' ), self::MESSAGE_STATUS_ERROR );
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				self::set_message( self::__( 'Could not process your request. You must be logged in.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( home_url(SF_Users::REGISTER_PATH) );	
				exit();
			}
			
		
		}
		
		//Handle any proposal forms
		self::handle_proposal_edit();
		self::handle_proposal_delete();
	}
	
	public static function is_proposal_edit_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['prop_edit'] ) ) ? TRUE : FALSE;
	}
	
	public static function handle_proposal_delete() {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_proposal_delete'] ) && wp_verify_nonce( $_POST['fv_proposal_delete_nonce'], 'fv_proposal_delete_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
			
			if ( $user_type == SF_Users::USER_TYPE_CONTRACTOR ) {
				
				$contractor_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
				
				//Check if this is the proposal owner
				if ( !empty( $_POST['fv_proposal_delete'] ) ) {
					$proposal_contractor_id = SF_Proposal::get_field($_POST['fv_proposal_delete'], 'contractor_id');
					if ( $proposal_contractor_id != $contractor_id ) {
						self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						return;
					}
				}
				
				//Delete the proposal
				$result = wp_delete_post ( $_POST['fv_proposal_delete'] );
				if ( $result ) {
					self::set_message( self::__( 'This proposal has been deleted' ), self::MESSAGE_STATUS_SUCCESS );
					
				} else {
					self::set_message( self::__( 'Could not delete the proposal.' ), self::MESSAGE_STATUS_ERROR );
				}
				
				$redirect = add_query_arg(array('action' => 'proposals'), SF_Users::user_profile_url());
				wp_redirect( $redirect );	
				exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function handle_proposal_edit() {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_proposal_edit'] ) && wp_verify_nonce( $_POST['fv_proposal_edit_nonce'], 'fv_proposal_edit_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
			
			if ( $user_type == SF_Users::USER_TYPE_CONTRACTOR ) {
				
				$contractor_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
				
				//Check if this is the proposal owner
				if ( !empty( $_POST['fv_proposal_edit'] ) ) {
					$proposal_contractor_id = SF_Proposal::get_field($_POST['fv_proposal_edit'], 'contractor_id');
					if ( $proposal_contractor_id != $contractor_id ) {
						self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						return;
					}
				}
				
				$result = self::process_proposal_form($user_id, $contractor_id);
				
				$redirect = add_query_arg(array('action' => 'jobs'), SF_Users::user_profile_url());
				wp_redirect( $redirect );	
				exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function process_proposal_form($user_id, $contractor_id) {
		$errors = array();
		
		$estimate = isset( $_POST['_estimate'] ) ?  stripslashes($_POST['_estimate']) : '';
		$description = isset( $_POST['post_content'] ) ?  stripslashes($_POST['post_content']) : '';
		$proposal_project_id = isset( $_POST['proposal_project_id'] ) ?  stripslashes($_POST['proposal_project_id']) : '';
		$proposal_message_id = isset( $_POST['proposal_message_id'] ) ?  stripslashes($_POST['proposal_message_id']) : '';
		
		if ( !$estimate  ) {
			$errors['empty_estimate'] = self:: __( 'Please type a estimate.' );
		}
		if ( !$description  ) {
			$errors['empty_description'] = self:: __( 'Please type a proposal description.' );
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			
			//Save it
			$data = array('message_id' => $proposal_message_id);
			
			//New proposal?
			if ( empty( $_POST['fv_proposal_edit'] ) ) {
				
				//Check if valid new proposal
				if ( !$proposal_project_id  ) {
					$errors['empty_proposal_project_id'] = self:: __( 'The project is not valid.' );
				}
				if ( $errors ) {
					foreach ( $errors as $error ) {
						self::set_message( $error, self::MESSAGE_STATUS_ERROR );
					}
					return FALSE;
				}
				
				//Valid
				$proposal_name = 'Proposal for '.get_the_title($proposal_project_id).' from '.get_the_title($contractor_id);
				
				//Save proposal
				$proposal_id = SF_Proposal::new_proposal($user_id, $contractor_id, $proposal_project_id, $proposal_name, $data);	
				
				//Send message
				if ( $proposal_id ) {
					
					//Get ids
					$project_facility_id = SF_Project::get_facility_id_for_project($proposal_project_id);
					
					//Prepare message
					$message_id = false;
					$from_user_id = $user_id;
					$from_id = $contractor_id;
					$to_id = $project_facility_id;
					
					$message_data = array();
					$message_data['type'] = 'proposal_send';
					
					//Overrides, set message override values
					$message_data['title'] = 'Contractor: '.get_the_title( $contractor_id ).' submitted a proposal for job: '.get_the_title($proposal_project_id);
					$message_data['content'] = 'Contractor: '.get_the_title( $contractor_id ).' submitted a proposal for job: '.get_the_title($proposal_project_id);
					
					$message_data['related_project_id'] = $proposal_project_id;
					$message_data['related_proposal_id'] = $proposal_id;
					//$message_data['related_project_action'] = $related_project_action;
					
					//Update original message data
					if ( $proposal_message_id ) {
						SF_Message::save_field($proposal_message_id, 'related_project_action', 'proposal_send'); //Mark original message as accepted
					}
					
					//Send message
					$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
					
				} 
				
				
			} else {
				$proposal_id = stripslashes($_POST['fv_proposal_edit']);
			}
			
			//Save fields
			SF_Proposal::save_form_fields($proposal_id);
			
			//Save uploads
			if ( !empty($_FILES) ) {
				$set_as_featured = false;
				foreach ($_FILES as $file_key => $file) {
					if ( !empty($file['name']) ) {
						$result = SF_Proposal::save_attachment( $proposal_id, $file_key, array(), $set_as_featured );
						if ( $result['result'] == TRUE ) {
							$set_as_featured = false;	
						} else {
							self::set_message( $result['error'], self::MESSAGE_STATUS_ERROR );	
						}
					}
				}
			}
			//Delete files
			if ( !empty( $_POST['delete_attachment'] ) ) {
				foreach (  $_POST['delete_attachment'] as $delete_attachment ) {
					if ( !empty( $delete_attachment ) ) {
						$result = wp_delete_attachment( $delete_attachment );
						if ( $result == FALSE ) {
							self::set_message( 'Your file could not be deleted.', self::MESSAGE_STATUS_ERROR );	
						} else {
							//successfully deleted file
							
						}
					}
				}
			
			}

			self::set_message( 'Your proposal has been sent.', self::MESSAGE_STATUS_SUCCESS );	
			
			return TRUE;
		}
	}
	
	
	
	// Proposal accept form -----
	
	public function load_proposal_accept_form() {
		global $facility_id, $proposal_fields;
		
		// If is proposal send form
		if ( self::is_proposal_accept_page() ) {
			
			if ( is_user_logged_in() ) {
				$facility_id = SF_Facility::get_facility_id_for_user();
				
				if ( $facility_id ) {
					
					if ( $_GET['prop_accept'] ) { //existing proposal id
						$proposal_fields = SF_Proposal::load_form_fields( $_GET['prop_accept'] );
					}  else {
						self::set_message( self::__( 'Could not process your request. Could not find the Proposal.' ), self::MESSAGE_STATUS_ERROR );
						return FALSE;
					}
					
				} else {
					self::set_message( self::__( 'Could not process your request. This user is not assigned to a Facility.' ), self::MESSAGE_STATUS_ERROR );
					wp_redirect( home_url() );	
					exit();
				}
			} else {
				self::set_message( self::__( 'Could not process your request. You must be logged in.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( home_url(SF_Users::REGISTER_PATH) );	
				exit();
			}
			
		
		}
		
		//Handle any proposal accept forms
		self::handle_proposal_accept();
	}
	
	public static function is_proposal_accept_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['prop_accept'] ) ) ? TRUE : FALSE;
	}
	
	public static function handle_proposal_accept() {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_proposal_accept'] ) && wp_verify_nonce( $_POST['fv_proposal_accept_nonce'], 'fv_proposal_accept_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
			
			if ( $user_type == SF_Users::USER_TYPE_FACILITY ) {
				
				$proposal_id = stripslashes($_POST['fv_proposal_accept']);
				$facility_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
				
				//Check if this is the proposal owner
				if ( !empty( $proposal_id ) ) {
					$proposal_project_id = SF_Proposal::get_field($proposal_id, 'project_id');
					$project_facility_id = SF_Project::get_field($proposal_project_id, 'facility_id');
					if ( $project_facility_id != $facility_id ) {
						self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						return;
					}
				}
				
				$result = self::process_proposal_accept_form($user_id, $facility_id, $proposal_id);
				
				$redirect = add_query_arg(array('action' => 'jobs'), SF_Users::user_profile_url());
				wp_redirect( $redirect );	
				exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function process_proposal_accept_form($user_id, $facility_id, $proposal_id) {
		$errors = array();
		
		//$estimate = isset( $_POST['_estimate'] ) ?  stripslashes($_POST['_estimate']) : '';
		$proposal_message_id =  isset( $_POST['proposal_message_id'] ) ?  stripslashes($_POST['proposal_message_id']) : '';
		$proposal_project_id =  isset( $_POST['proposal_project_id'] ) ?  stripslashes($_POST['proposal_project_id']) : '';
		
		if ( !$proposal_id  ) {
			$errors['empty_proposal_id'] = self:: __( 'The selected proposal is invalid.' );
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			
			//Save it
			
			//Get ids
			$proposal_project_id = SF_Proposal::get_project_id_for_proposal($proposal_id);
			$contractor_id = SF_Proposal::get_contractor_id_for_proposal($proposal_id);
			
			//Set the Project Proposal
			$accept_result = SF_Project::set_project_proposal($proposal_project_id, $proposal_id);
			
			//Check result
			if ( $accept_result['result'] == true ) {
				
				//Prepare message
				$message_id = false;
				$from_user_id = $user_id;
				$from_id = $facility_id;
				$to_id = $contractor_id;
				
				$message_data = array();
				$message_data['type'] = 'proposal_accept';
				
				//Overrides, set message override values
				$message_data['title'] = 'Facility: '.get_the_title( $user_type_data['user_type_id'] ).' accepted your proposal for job: '.get_the_title($proposal_project_id);
				$message_data['content'] = 'Facility: '.get_the_title( $user_type_data['user_type_id'] ).' accepted your proposal for job: '.get_the_title($proposal_project_id);
				
				$message_data['related_project_id'] = $proposal_project_id;
				$message_data['related_proposal_id'] = $proposal_id;
				//$message_data['related_project_action'] = $related_project_action;
				
				//Update original message data
				if ( $proposal_message_id ) {
					SF_Message::save_field($proposal_message_id, 'related_project_action', 'proposal_accept'); //Mark original message as accepted
				}
				
				//Send message
				$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
				
			} else {
				
				if ( $accept_result['message'] == 'assigned' ) {
					self::set_message( self:: __( 'Error: Could not accept job. This job is already assigned to a Contractor.' ), self::MESSAGE_STATUS_ERROR );
				} elseif ( $accept_result['message'] == 'invalidtype' ) {
					self::set_message( self:: __( 'Error: Could not accept job. This must be a valid Proposal to accept.' ), self::MESSAGE_STATUS_ERROR );
				} elseif ( $accept_result['message'] == 'missingvalues' ) {
					self::set_message( self:: __( 'Error: Could not accept job. The request is missing required fields.' ), self::MESSAGE_STATUS_ERROR );
				} elseif ( $accept_result['message'] == 'invalid' ) {
					self::set_message( self:: __( 'Error: Could not accept job. Your request is invalid.' ), self::MESSAGE_STATUS_ERROR );
				} else {
					self::set_message( self:: __( 'Error: Could not accept job. Your request is invalid.' ), self::MESSAGE_STATUS_ERROR );
				}
				return FALSE; //Don't continue any further
			}
			

			self::set_message( 'The proposal has been accepted.', self::MESSAGE_STATUS_SUCCESS );	
			
			return TRUE;
		}
	}
	
	// Proposal view -----
	public function load_proposal_view() {
		global $contractor_id, $facility_id, $proposal_fields;
		
		// If is proposal send form
		if ( self::is_proposal_view_page() ) {
			
			$proposal_id = trim($_GET['prop_view']);
			
			if ( is_user_logged_in() && !empty($proposal_id) ) {
				
				$user_id =  get_current_user_id();
				$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
				
				if ( $user_type == SF_Users::USER_TYPE_FACILITY ) {
						
					$facility_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
					
					//Check if this is the proposal owner
					$proposal_project_id = SF_Proposal::get_field($proposal_id, 'project_id');
					$project_facility_id = SF_Project::get_field($proposal_project_id, 'facility_id');
					if ( empty($facility_id) || empty($project_facility_id) || $project_facility_id != $facility_id ) {
						self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						wp_redirect( home_url() );	
						exit();
					}
					
				} elseif ( $user_type == SF_Users::USER_TYPE_CONTRACTOR ) {
				
					$contractor_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
					
					//Check if this is the proposal owner
					$proposal_contractor_id = SF_Proposal::get_field($proposal_id, 'contractor_id');
					if ( $proposal_contractor_id != $contractor_id ) {
						self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						wp_redirect( home_url() );	
						exit();
					}
					
				} else {
					self::set_message( self::__( 'Could not process your request. This proposal is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
					wp_redirect( home_url() );	
					exit();
				}
				
			} else {
				self::set_message( self::__( 'Could not process your request. You must be logged in.' ), self::MESSAGE_STATUS_ERROR );
				wp_redirect( home_url(SF_Users::REGISTER_PATH) );	
				exit();
			}

		}
		
	}
	
	public static function is_proposal_view_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['prop_view'] ) ) ? TRUE : FALSE;
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
