<?php

class SF_Tasks extends SF_FV {
	
	const OPTION_LAST_ADMIN_REFERENCES_REVIEW = 'fv_last_admin_references_review';

	public static function init() {
		
		// Scheduled task
		add_action( 'sf_fv_hourly_event', array(get_class(), 'run_hourly_events') );
	
	}

	// Hourly schedule
	public function run_hourly_events() {
		//Run
		
		//self::send_project_proposal_reminders(); //Disabled - not used 
		self::handle_projects_ending_soon();
		self::handle_references_admin_review();	
	}
	
	private function handle_references_admin_review() {
		
		$last_review = get_option(self::OPTION_LAST_ADMIN_REFERENCES_REVIEW, 0);
		$next_review = $last_review + ( 86400 * 7 ); //send every 7 days 
		//Have we passed the next review period
		if ( time() < $next_review ) { 
			return;
		}
		
		//Set review time
		update_option(self::OPTION_LAST_ADMIN_REFERENCES_REVIEW, time());
		
		$reference_to_review_count = 0;
		
		//Find projects that have the admin review meta
		$args = array(
			'post_type' => SF_Contractor::POST_TYPE,
			'post_status' => 'any',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'fv_bypass_filter' => TRUE,
			'meta_query' => array(
				array(
					'key' => '_category_references_admin_review',
					//'value' => '1', //If version is less than WP 3.9, then a value is required for EXISTS
					'compare' => 'EXISTS',
				),
			)
		);

		$result = get_posts( $args );

		if ( !empty( $result ) ) {
			foreach ( $result as $contractor_id ) {
				//Search for one review
				$reference_to_review = SF_Contractor::get_field_multiple($contractor_id, 'category_references_admin_review');
				if ( !empty($reference_to_review) && sizeof( $reference_to_review ) > 0 ) {
					$reference_to_review_count++;
				}
			}
		}
		
		//We found at least one reference to review
		$review_csv_url = site_url('/wp-admin/admin.php').'?page=sf-fv-references-review&review_references_csv='.time();
		if ( $reference_to_review_count > 0) {
			$subject = 'Weekly Reference Reviews - '.intval($reference_to_review_count).' to review';

			$content = 'There are '.intval($reference_to_review_count).' references to review.'."\n";
			$content .= 'Review: <a href="'.$review_csv_url.'">'.$review_csv_url.'</a> '."\n";
			$content .= '-----'."\n";
			$content .= 'Sent from: '.get_option('blogname')."\n";
		} else {
			
			$subject = 'Weekly Reference Reviews - NO references to review';
			
			$content = 'There are '.intval($reference_to_review_count).' references to review.'."\n";
			$content .= 'Review: <a href="'.$review_csv_url.'">'.$review_csv_url.'</a> '."\n";
			$content .= '-----'."\n";
			$content .= 'Sent from: '.get_option('blogname')."\n";
		}
		
		$admin_email = get_option('admin_email');
		//$admin_email = 'daniel@studiofidelis.com'; //test email

		$email_data = array(
			'to_email' => $admin_email,
			'from_email' => self::$notification_from_email,
			'from_name' => self::$notification_from_name,
			'subject' => $subject,
			'content' => $content,
			'is_html' => self::$notification_format_is_html
		);
		
		$result = SF_FV::send_email($email_data);
	}
	
	private function handle_projects_ending_soon() {
		
		//Find projects that have a proposal id but are not yet marked as completed
		$args = array(
			'post_type' => SF_Project::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'fv_bypass_filter' => TRUE,
			'meta_query' => array(
				array(
						'key' => '_project_step',
						'value' => 'completed', 
						'compare' => '!=',
				),
				array(
						'key' => '_proposal_id',
						'value' => 0, 
						'compare' => '>',
						'type' => 'numeric'
				),
			)
		);

		$result = get_posts( $args );

		if ( !empty( $result ) ) {
			foreach ( $result as $project_id ) {
				
				//Is Project past deadline
				$deadline = strtotime(SF_Project::get_field($project_id, 'deadline'));
				
				if ( time() >= $deadline ) {
					
					$contractor_id = SF_Project::get_contractor_id_for_project($project_id);
					$facility_id = SF_Project::get_facility_id_for_project($project_id);
					
					if ( self::DEBUG ) error_log('send_project_completed_message to'.$contractor_id.' for project_id'.$project_id);
					
					self::send_project_completed_message($project_id, $contractor_id, $facility_id);
					
					//Set as completed
					update_post_meta($project_id, '_project_step', 'completed'); 
					
				} else {
					
					//Not yet past deadline	
					
					$project_step = SF_Project::get_field($project_id, 'project_step');
					
					if ( (time() + 86400) >= $deadline && ( $project_step != 'deadline_1day' && $project_step != 'completed') ) { // deadline is in 24 hours in seconds ( 86400 )
						//Send
						$contractor_id = SF_Project::get_contractor_id_for_project($project_id);
						$facility_id = SF_Project::get_facility_id_for_project($project_id);
						$title = 'Final deadline reminder';
						
						if ( self::DEBUG ) error_log('send_deadline_reminder_message 24hrs to'.$contractor_id.' for project_id'.$project_id);
						
						self::send_project_deadline_reminder_message( $project_id, $contractor_id, $facility_id, $title );
						
						//Set project step
						update_post_meta($project_id, '_project_step', 'deadline_1day'); 
						
					} elseif ( (time() + 259200) >= $deadline && ( $project_step != 'deadline_3days' && $project_step != 'deadline_1day' && $project_step != 'completed') ) { //deadline is in 3 days in seconds ( 259200 )
						
						//Send
						$contractor_id = SF_Project::get_contractor_id_for_project($project_id);
						$facility_id = SF_Project::get_facility_id_for_project($project_id);
						$title = '3 day deadline reminder';
						
						if ( self::DEBUG ) error_log('send_deadline_reminder_message 3days to'.$contractor_id.' for project_id'.$project_id);
						self::send_project_deadline_reminder_message( $project_id, $contractor_id, $facility_id, $title);
						
						//Set project step
						update_post_meta($project_id, '_project_step', 'deadline_3days'); 
					}
				}
			}
		}
	}
	
	//Disabled - not used
	private function send_project_proposal_reminders() {
		
		//Find projects that don't have a proposal id
		$args = array(
			'post_type' => SF_Project::POST_TYPE,
			'post_status' => 'publish',
			'posts_per_page' => -1,
			'fields' => 'ids',
			'fv_bypass_filter' => TRUE,
			'meta_query' => array(
				array(
						'key' => '_proposal_id',
						'value' => 0, //set to anything for NOT EXISTS
						'compare' => 'LIKE',
						'type' => 'numeric'
				),
			)
		);

		$result = get_posts( $args );
		
		if ( !empty( $result ) ) {
			foreach ( $result as $project_id ) {
				//Make sure project isn't past deadline yet
				$deadline = strtotime(SF_Project::get_field($project_id, 'deadline'));
				if ( time() <= $deadline ) {
					
					//Get invited contractors
					$invites = SF_Project::get_field_multiple($project_id, 'invited_contractors_accepted');
					$avoid_duplicates = array();
					if ( !empty($invites) ) {
						$facility_id = SF_Project::get_field($project_id, 'facility_id');
						$facility_title = ($facility_id) ? get_the_title($facility_id) : 'Facility';
						$project_title = get_the_title($project_id);
						foreach ( $invites as $invite_json ) {
							//Check if valid invite
							$invite = json_decode($invite_json, true);
							if ( $invite['contractor_id'] && $invite['date'] && !in_array($invite['contractor_id'], $avoid_duplicates) ) {
								
								//Get proposals for this contractor for this project
								$proposal_ids = self::lookup_by_meta( SF_Proposal::POST_TYPE, array( '_contractor_id' => $invite['contractor_id'], '_project_id' => $project_id) );
								
								//Check if 24 hour reminder needs to be sent
								if ( empty($proposal_ids) ) { //No proposals sent
								
									if ( empty($invite['reminder']) && time() >= ($invite['date'] + 86400) ) { //date sent + 24 hours in seconds ( 86400 )
										//Send
										if ( self::DEBUG ) error_log('send_reminder_message 24hrs to'.$invite['contractor_id'].' for project_id'.$project_id);
										self::send_proposal_reminder_message($invite['contractor_id'], $project_id, $facility_id, $project_title, $facility_title);
										//record as sent
										$upate_invite_record = $invite;
										$upate_invite_record['reminder'] = 1;
										update_post_meta($project_id, '_invited_contractors_accepted', json_encode($upate_invite_record), $invite_json); //replace existing invite record
									} elseif ( $invite['reminder'] == 1 && time() >= ($invite['date'] + 259200) ) { //date sent + 3 days in seconds ( 259200 )
										//Send
										if ( self::DEBUG ) error_log('send_reminder_message 3days to'.$invite['contractor_id'].' for project_id'.$project_id);
										self::send_proposal_reminder_message($invite['contractor_id'], $project_id, $facility_id, $project_title, $facility_title);
										//record as sent
										$upate_invite_record = $invite;
										$upate_invite_record['reminder'] = 2;
										update_post_meta($project_id, '_invited_contractors_accepted', json_encode($upate_invite_record), $invite_json); //replace existing invite record
									}
								} 
								$avoid_duplicates[] = $invite['contractor_id'];
							}
		
						}		
					}
				}
			}
		}
	}
	
	private function send_proposal_reminder_message($contractor_id, $project_id, $facility_id, $project_title, $facility_title) {
			
		//Prepare message
		$from_user_id = -1; //from server
		$from_id = $facility_id;
		$to_id = $contractor_id;
		
		$message_data = array();
		$message_data['type'] = 'proposal_reminder';
		$message_data['title'] = 'Please send your proposal for '.$project_title.'!';
		$message_data['content'] = 'Facility: '.$facility_title.' invites you to submit a proposal on the job: '.$project_title;
		$message_data['related_project_id'] = $project_id;
		//$message_data['related_project_action'] = $related_project_action;
		
		//Send
		$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
		
	}
	
	private function send_project_deadline_reminder_message( $project_id, $contractor_id, $facility_id, $title ) {
			
		//Prepare message
		$from_user_id = -1; //from server
		$from_id = $facility_id;
		$to_id = $contractor_id;
		
		$message_data = array();
		$message_data['type'] = 'project_deadline_reminder';
		$message_data['title'] =  $title.' | '.get_the_title($project_id);
		$message_data['content'] = 'This is a reminder that the job: '.get_the_title($project_id).' ends soon.';
		$message_data['related_project_id'] = $project_id;
		//$message_data['related_project_action'] = $related_project_action;
		
		//Send
		$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
		
	}
	
	private function send_project_completed_message($project_id, $contractor_id, $facility_id) {
			
		//Prepare message to Facility
		$from_user_id = -1; //from server
		$from_id = $facility_id; //also from facility?
		$to_id = $facility_id;
		
		$message_data = array();
		$message_data['type'] = 'project_completed';
		$message_data['title'] = 'Job completed '.get_the_title($project_id);
		$message_data['content'] = 'The job: '.get_the_title($project_id).' has reached the deadline and is now completed.';
		$message_data['related_project_id'] = $project_id;
		//$message_data['related_project_action'] = $related_project_action;
		
		//Send
		$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
		
		//Prepare message to Contractor
		
		$from_user_id = -1; //from server
		$from_id = $facility_id;
		$to_id = $contractor_id;
		
		$message_data = array();
		$message_data['type'] = 'project_completed';
		$message_data['title'] = 'Job completed '.get_the_title($project_id);
		$message_data['content'] = 'The Job: '.get_the_title($project_id).' has passed the deadline and is now completed.';
		$message_data['related_project_id'] = $project_id;
		//$message_data['related_project_action'] = $related_project_action;
		
		//Send
		$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
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
