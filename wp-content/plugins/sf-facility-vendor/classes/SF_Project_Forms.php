<?php

class SF_Project_Forms extends SF_FV {
	
	private static $instance;

	public static function init() {
		
		// Project (job) Edit
		add_action( 'template_redirect', array( get_class(), 'load_project_edit'), 11);

		
	}
	
	// Project Edit
	public function load_project_edit() {
		global $facility_id, $contractor_id, $project_fields;
		
		// If is edit
		if ( self::is_project_edit_page() ) {
			
			if ( is_user_logged_in() ) {
				$facility_id = SF_Facility::get_facility_id_for_user();
				
				if ( $facility_id ) {
					
					if ( $_GET['job_edit'] ) { //existing project id
						$project_fields = SF_Project::load_form_fields( $_GET['job_edit'] );
					} else {
						$project_fields = SF_Project::load_form_fields();
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
		
		//Handle any project forms
		self::handle_project_edit();
		self::handle_project_delete();
	}
	
	public static function is_project_edit_page() {
		//return is_page_template( 'template-facility-profile.php' );
		return ( isset( $_GET['job_edit'] ) ) ? TRUE : FALSE;
	}
	
	public static function handle_project_delete() {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_project_delete'] ) && wp_verify_nonce( $_POST['fv_project_delete_nonce'], 'fv_project_delete_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
			
			if ( $user_type == SF_Users::USER_TYPE_FACILITY ) {
				
				$facility_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
				
				//Check if this is the project owner
				if ( !empty( $_POST['fv_project_delete'] ) ) {
					$project_facility_id = SF_Project::get_field($_POST['fv_project_delete'], 'facility_id');
					if ( empty($project_facility_id) || $project_facility_id != $facility_id ) {
						self::set_message( self::__( 'Could not process your request. This job is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						wp_redirect( home_url() );
						exit();
					}
				}
				
				//Delete the project
				$result = wp_delete_post ( $_POST['fv_project_delete'] );
				if ( $result ) {
					self::set_message( self::__( 'This job has been deleted' ), self::MESSAGE_STATUS_SUCCESS );
					
				} else {
					self::set_message( self::__( 'Could not delete the job.' ), self::MESSAGE_STATUS_ERROR );
				}
				
				$redirect = add_query_arg(array('action' => 'jobs'), SF_Users::user_profile_url());
				wp_redirect( $redirect );	
				exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function handle_project_edit() {
		
		// Edit form Attempt
		if ( isset( $_POST['fv_project_edit'] ) && wp_verify_nonce( $_POST['fv_project_edit_nonce'], 'fv_project_edit_nonce' ) && is_user_logged_in() ) {
			
			//Get account type
			$user_id =  get_current_user_id();
			$user_type = get_user_meta( $user_id, SF_Users::USER_TYPE_META_KEY, true);
			
			if ( $user_type == SF_Users::USER_TYPE_FACILITY ) {
				
				$facility_id = get_user_meta( $user_id, SF_Users::USER_TYPE_ID_META_KEY, true);
				
				//Check if this is the project owner
				if ( !empty( $_POST['fv_project_edit'] ) ) {
					$project_facility_id = SF_Project::get_field($_POST['fv_project_edit'], 'facility_id');
					if ( empty($project_facility_id) || $project_facility_id != $facility_id ) {
						self::set_message( self::__( 'Could not process your request. This job is not assigned to your account.' ), self::MESSAGE_STATUS_ERROR );
						wp_redirect( home_url() );
						exit();
					}
				}
				
				$result = self::process_project_form($user_id, $facility_id);
				
				$redirect = add_query_arg(array('action' => 'jobs'), SF_Users::user_profile_url());
				wp_redirect( $redirect );	
				exit();
			
			} else {
				self::set_message( self::__( 'Could not process your request.' ), self::MESSAGE_STATUS_ERROR );
			}
		} 
		
	}
	
	
	public static function process_project_form($user_id, $facility_id) {
		$errors = array();
		
		$project_name = isset( $_POST['post_title'] ) ?  stripslashes($_POST['post_title']) :'';
		$budget = isset( $_POST['_budget'] ) ?  stripslashes($_POST['_budget']) : '';
		$location = isset( $_POST['_budget'] ) ? stripslashes($_POST['_location']) : '';
		$location_zip = isset( $_POST['_location_zip'] ) ?  stripslashes($_POST['_location_zip']) : '';
		$deadline = isset( $_POST['_deadline'] ) ?  stripslashes($_POST['_deadline']) : '';
		$description = isset( $_POST['post_content'] ) ?  stripslashes($_POST['post_content']) : '';
		
		if ( !$project_name ) {
			$errors['empty_project_name'] = self:: __( 'Please type a project name.' );
		}
		if ( !$location  ) {
			$errors['empty_location'] = self:: __( 'Please type a location (city, state).' );
		}
		if ( !$location_zip  ) {
			$errors['empty_location_zip'] = self:: __( 'Please type a location zip code.' );
		}
		if ( !$budget  ) {
			$errors['empty_budget'] = self:: __( 'Please type a budget.' );
		}
		if ( !$deadline  ) {
			$errors['empty_deadline'] = self:: __( 'Please type a deadline.' );
		}
		if ( !$description  ) {
			$errors['empty_description'] = self:: __( 'Please type a project description.' );
		}
		
		if ( $errors ) {
			foreach ( $errors as $error ) {
				self::set_message( $error, self::MESSAGE_STATUS_ERROR );
			}
			return FALSE;
		} else {
			//Save it
			
			//New project?
			if ( empty( $_POST['fv_project_edit'] ) ) {
				$project_id = SF_Project::new_project($user_id, $facility_id, $project_name);	
			} else {
				$project_id = $_POST['fv_project_edit'];
			}
			
			//Save fields
			SF_Project::save_form_fields($project_id);
			
			//Set as featured image
			if ( !empty( $_POST['set_as_featured'] ) ) {
				update_post_meta( $project_id, '_thumbnail_id', intval($_POST['set_as_featured']) );	
			}
			
			//Save uploads
			if ( !empty($_FILES) ) {
				//set first as featured (only if a featured doesn't already exist)
				$set_as_featured = ( get_post_meta( $project_id, '_thumbnail_id', true) ) ? false : true; 
				foreach ($_FILES as $file_key => $file) {
					if ( !empty($file['name']) ) {
						$result = SF_Project::save_attachment( $project_id, $file_key, array(), $set_as_featured );
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
							if ( get_post_meta( $project_id, '_thumbnail_id', true ) == $delete_attachment ){ 
								update_post_meta( $project_id, '_thumbnail_id', false ); //if it was featured then remove the featured id
							}
						}
					}
				}
			
			}

			self::set_message( 'Your job has been saved.', self::MESSAGE_STATUS_SUCCESS );	
			
			return TRUE;
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
