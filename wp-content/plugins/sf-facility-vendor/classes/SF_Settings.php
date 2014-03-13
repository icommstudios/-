<?php

class SF_Settings extends SF_FV {
	
	private static $error_message;
	private static $success_message;
	
	public static function init() {
	
		if ( is_admin() ) {
			
			//Add settings menu
			add_action('admin_menu',  array( get_class(), 'custom_create_options_menu'));
			//call register settings function
			add_action( 'admin_init', array(get_class(), 'register_mysettings') );
			
			//Import
			add_action('admin_menu', array( get_class(), 'sf_fv_import_admin_actions' ) ); //Add Menu
			//Add action to run import - run at wp_loaded to ensure all custom posts, taxonomies are registered
			add_action('wp_loaded', array( get_class(), 'handle_import'), 999 );
		}
		
	}
	
	public static function custom_create_options_menu() {

		//Create Options menu
		add_options_page('FV - Settings', 'Facility Vendor - Settings', 'administrator', 'fv-settings', array(get_class(), 'custom_fv_settings_page'));
	
		
	}
	
	public static function register_mysettings() {
		//register email subjects
		register_setting( 'fv-settings', self::$notification_email_subject_key['reset_password'] );
		register_setting( 'fv-settings', self::$notification_email_subject_key['registration'] );
		register_setting( 'fv-settings', self::$notification_email_subject_key['message_notification'] );
		//register email contents
		register_setting( 'fv-settings', self::$notification_email_content_key['reset_password'] );
		register_setting( 'fv-settings', self::$notification_email_content_key['registration'] );
		register_setting( 'fv-settings', self::$notification_email_content_key['message_notification'] );
		
		//register other settings
		register_setting( 'fv-settings', 'fv_search_radius_api' );
		register_setting( 'fv-settings', 'fv_paypal_id' );
		/*
		//NOT Used
		register_setting( 'fv-settings', 'fv_paypal_api_key' );
		register_setting( 'fv-settings', 'fv_api_password' );
		register_setting( 'fv-settings', 'fv_paypal_api_signature' );
		*/
		
	}
	
	public static function custom_fv_settings_page() {
		$url_paypal_ipn = SF_Users::$url_paypal_ipn;
		?>
        <h2>Facility Vendor - Settings</h2>
		<div class="wrap">
		<form method="post" action="options.php">
     
            <?php settings_fields('fv-settings'); ?>
			
            <h3>Email Content</h3>
            
            <em>The email sent when the user resets their password. Shortcodes: <?php 
					//show all codes for this email
					foreach ( self::$notification_email_replace_codes['reset_password'] as $code_key ) {
						echo '['.$code_key.'] &nbsp;';
					}
					?></em>
			<table class="form-table">
				<tr valign="top">
                    <th scope="row">Reset Password - Subject</th>
                    <td><input type="text" width="100" style="width: 100%;" name="<?php echo self::$notification_email_subject_key['reset_password']; ?>" value="<?php echo get_option(self::$notification_email_subject_key['reset_password']); ?>" /></td>
				</tr>
				<tr valign="top">
                    <th scope="row">Reset Password - Content<br>
                    <div style="margin-left: 5px;"><small> Shortcodes: <br><?php 
					//show all codes for this email
					foreach ( self::$notification_email_replace_codes['reset_password'] as $code_key ) {
						echo '['.$code_key.'] &nbsp;';
					}
					?></small>
                    </div></th>
                    <td><textarea style="width: 100%; height: 100px;" name="<?php echo self::$notification_email_content_key['reset_password']; ?>" ><?php echo esc_html(get_option(self::$notification_email_content_key['reset_password'])); ?></textarea></td>
				</tr>
			</table>
            <em>The email sent when the user registers. Shortcodes: <?php 
					//show all codes for this email
					foreach ( self::$notification_email_replace_codes['registration'] as $code_key ) {
						echo '['.$code_key.'] &nbsp;';
					}
					?></em>
            <table class="form-table">
				<tr valign="top">
                    <th scope="row">Registration - Subject</th>
                    <td><input type="text" width="100" style="width: 100%;" name="<?php echo self::$notification_email_subject_key['registration']; ?>" value="<?php echo get_option(self::$notification_email_subject_key['registration']); ?>" /></td>
				</tr>
				<tr valign="top">
                    <th scope="row">Registration - Content</th>
                    <td><textarea style="width: 100%; height: 100px;" name="<?php echo self::$notification_email_content_key['registration']; ?>" ><?php echo esc_html(get_option(self::$notification_email_content_key['registration'])); ?></textarea></td>
				</tr>
			</table>
             <em>The email sent when the user has a new Message on their profile. Shortcodes: <?php 
					//show all codes for this email
					foreach ( self::$notification_email_replace_codes['message_notification'] as $code_key ) {
						echo '['.$code_key.'] &nbsp;';
					}
					?></em>
              <table class="form-table">
				<tr valign="top">
                    <th scope="row">Message Notification - Subject</th>
                    <td><input type="text" width="100" style="width: 100%;" name="<?php echo self::$notification_email_subject_key['message_notification']; ?>" value="<?php echo get_option(self::$notification_email_subject_key['message_notification']); ?>" /></td>
				</tr>
				<tr valign="top">
                    <th scope="row">Message Notification - Content</th>
                    <td><textarea style="width: 100%; height: 100px;" name="<?php echo self::$notification_email_content_key['message_notification']; ?>" ><?php echo esc_html(get_option(self::$notification_email_content_key['message_notification'])); ?></textarea></td>
				</tr>
			</table>
            
           
            <h3>PayPal Settings</h3>
            
            <table class="form-table">
            <tr valign="top">
                    <th scope="row">PayPal ID (Email)</th>
                    <td><input type="text" width="100" style="width: 300px;" name="fv_paypal_id" value="<?php echo get_option('fv_paypal_id'); ?>" /><br><em>The email address you use to log into your Paypal account with.</em></td>
				</tr>
                <?php
				//NOT Used
				/*
				<tr valign="top">
                    <th scope="row">PayPal API KEY</th>
                    <td><input type="text" width="100" style="width: 300px;" name="fv_paypal_api_key" value="<?php echo get_option('fv_paypal_api_key'); ?>" /></td>
				</tr>
				<tr valign="top">
                    <th scope="row">PayPal API PASSWORD</th>
                    <td><input type="text" width="100" style="width: 300px;" name="fv_paypal_api_password" value="<?php echo get_option('fv_paypal_api_password'); ?>" /></td>
				</tr>
				<tr valign="top">
                    <th scope="row">PayPal API SIGNATURE</th>
                    <td><input type="text" width="100" style="width: 500px;" name="fv_paypal_api_signature" value="<?php echo get_option('fv_paypal_api_signature'); ?>" /></td>
				</tr>
				*/
				?>
               <tr valign="top">
                    <th scope="row">IPN Url (for PayPal Payment Notifications )</th>
                    <td><input type="text" width="100" disabled="disabled" style="width: 500px;" name="fv_paypal_ipn_url" value="<?php echo $url_paypal_ipn; ?>" /> <br><em>Set this URL as your IPN url on your PayPal account.</em></td>
				</tr>
			</table>
            
            <h3>Search Radius API ( Zip Code Distance )</h3>
             
            <table class="form-table">
            <tr valign="top">
                    <th scope="row">Zip Code Distance API Key </th>
                    <td><input type="text" width="100" style="width: 500px;" name="fv_search_radius_api" value="<?php echo get_option('fv_search_radius_api'); ?>" /><br><em>API available at: <a target="_blank" href="http://zipcodedistanceapi.redline13.com/">http://zipcodedistanceapi.redline13.com/</a></em></td>
				</tr>
			</table>
			
			
			<?php submit_button(); ?>
		
		</form>
		</div>
		<?php 
	} 
	
	private function send_website_invite_message( $listing_id, $to_email ) {
			
		//Prepare message 
		$from_user_id = -1; //from server
		$from_id = -1; //from server
		$to_id = $listing_id;
		
		$message_data = array();
		$message_data['do_not_email'] = true;
		$message_data['type'] = 'welcome';
		$message_data['title'] = 'Welcome to Alliance Facility Solutions!';
		$message_data['content'] = 'Welcome to Alliance Facility Solutions!<br> Your free listing for '.get_the_title($listing_id).' has been created!';
		//$message_data['related_project_id'] = $project_id;
		//$message_data['related_project_action'] = $related_project_action;
		
		//Send
		$message_id = SF_Message::new_message( $from_user_id, $from_id, $to_id, $message_data);
		
		
		//Send message notification email
		
		//Generate claim listing code
		$claim_listing_array = array();
		$claim_listing_array['listing_id'] = $listing_id;
		$claim_listing_array['type'] = get_post_type($listing_id);
		$claim_listing_array['date'] = time();
		$claim_listing = base64_encode(json_encode($claim_listing_array)); 
		
		$message_title = 'Claim your Listing! | '.get_option('blogname');
		$message_link = add_query_arg( array( 'claim_listing' => $claim_listing ), home_url(SF_Users::REGISTER_PATH));
		
		$email_replace_keys = array('user_email' => $to_email, 'site_name' => get_option('blogname'), 'site_url' => home_url(), 'message_link' => $message_link, 'message_title' => $message_title );
		$email_data = array(
			'to_email' => $to_email,
			'from_email' => self::$notification_from_email,
			'from_name' => self::$notification_from_name,
			'subject' => $message_title, // self::build_email_subject('message_notification', $email_replace_keys),
			'content' => self::build_email_content('message_notification', $email_replace_keys),
			'is_html' => self::$notification_format_is_html
		);
		
		$result = SF_FV::send_email($email_data);
		
	}
	
	public static function convert_csv_to_array($filename='', $delimiter=',') {
			if(!file_exists($filename) || !is_readable($filename))
				return FALSE;
		
			$header = NULL;
			$data = array();
			if (($handle = fopen($filename, 'r')) !== FALSE)
			{
				while (($row = fgetcsv($handle, 1000, $delimiter)) !== FALSE)
				{
					if(!$header)
						$header = $row;
					else
						$data[] = array_combine($header, $row);
				}
				fclose($handle);
			}
			return $data;
		}
		
	
	//Run import CSV
	public static function import_csv($file_path = NULL) {
		
		//$file = file_get_contents("product.XML");
		//$file_path = dirname(__FILE__)."/Download.csv";
		
		$csv = false;
		
		//Get data from file
		if (file_exists($file_path)) {
			
			ini_set("auto_detect_line_endings", true);
			
			//$csv = self::custom_str_getcsv(file_get_contents($file_path), $delimiter = ',', $enclosure = '"', $escape = '\\');
			//$csv = str_getcsv( file_get_contents($file_path), $delimiter = ',', $enclosure = '"');
			/*
			if (!function_exists('str_getcsv')) {  //If not PHP 5.3 +
				$csv = self::str_getcsv(file_get_contents($file_path), ',', '"');
			} else {
				if (self::DEBUG) error_log("str_getcsv");
				$csv = str_getcsv(file_get_contents($file_path), ',', '"');
			}
			*/
			/*
			if ($csv) {
				if (self::DEBUG) error_log("convert_csv_results_to_array");
				//$csv = self::convert_csv_results_to_array( $csv );
			}
			*/
	
			$csv = self::convert_csv_to_array($file_path);
		
		}
		

		//Do we have data?
		if ( is_array($csv) && !empty($csv) ) {
			
			//Generate an import Batch ID (stored later)
			$batch_id = 'batch-address-import-'.time().'-'.mt_rand();
			$batch_timestamp = time();
			
			$log_entries = array();
			$log_entries_count = 0;
			$log_skipped_invalid_count = 0;
			$log_skipped_already_exists_count = 0;
			$log_skipped_already_exists_array = array();

			
			//For each
			$arrays_duplicate_record_stop =  array();
			foreach ($csv as $line) {
				
				//Columns
				// Name, Company, City, State, Zip, Email, Phone, Industry, Type
			   
			   //Check if valid
			   if ( !empty( $line["Email"] ) && stripos($line["Email"], '@') !== false && !empty($line["Company"]) ) {
				   
				   $email_address = trim($line["Email"]);
				   
				   if ( !in_array($email_address, $arrays_duplicate_record_stop) ) { //If we've already done this order 
				   
					   //Check if already exists
					   $email_exists = email_exists( $email_address ); //check if user has email
					   if ( !$email_exists ) {
							//Check if post id has email
							$post_email_exists = self::lookup_by_meta( array(SF_Contractor::POST_TYPE, SF_Facility::POST_TYPE), array('_email' => $email_address));
							if ( empty($post_email_exists) ) {
								$email_exists = false;
							} else {
								$email_exists = true;
							}
					   }
					  
					   if ( empty($email_exists) ) { //If an existing email does not exist, then lets continue
							
							$address_country = $line["Country"];
							
							$data = array();
							$data['name'] = trim($line["Name"]);
							$data['company'] = trim($line["Company"]);
							$data['location'] = trim($line["City"].' '.$line["State"]);
							$data['location_zip'] = trim($line["Zip"]);
							$data['phone'] = trim($line["Phone"]);
							if ( !empty($line["Industry"]) ) {
								$data['taxonomy_type'][] = trim($line["Industry"]);
							}
							if ( !empty($line["Type"]) ) {
								$data['taxonomy_type'][] = trim($line["Type"]);
							}
							 
							
							//If not a test
							$id = false; //reset id
							if ($_POST['import_mode'] == 'import') { 
							
								//Create Contractor
								if ( $_POST['listing_type'] == SF_Contractor::POST_TYPE ) {
									
									$post = array(
										'post_title' => $data['company'],
										'post_name' => sanitize_title($data['company']),
										'post_status' => 'publish',
										'post_type' => SF_Contractor::POST_TYPE,
									);
								
									$id = wp_insert_post( $post );
									if ( !is_wp_error( $id ) ) {
										
										SF_Contractor::save_field($id, 'email', $email_address);
										SF_Contractor::save_field($id, 'name', $data['name']);
										SF_Contractor::save_field($id, 'phone', $data['phone']);
										SF_Contractor::save_field($id, 'website', $data['website']);
										SF_Contractor::save_field($id, 'company', $data['company']);
										SF_Contractor::save_field($id, 'location', $data['location']);
										SF_Contractor::save_field($id, 'location_zip', $data['location_zip']);
										
										if ( !empty( $data['taxonomy_type'] ) ) {
											$selected_cats = array();
											foreach ( $data['taxonomy_type'] as $each_term ) {
												$each_term = trim($each_term);
												if ( !empty($each_term)) {
													$existing_term = get_term_by('name', $each_term, SF_Taxonomies::JOB_TYPE_TAXONOMY );
													if ( $existing_term && isset($existing_term->term_id) ) {
														$selected_cats[] = $existing_term->term_id;
													} else {
														$create_term = wp_insert_term($each_term, SF_Taxonomies::JOB_TYPE_TAXONOMY);
														if ( isset($create_term['term_id']) ) {
															$selected_cats[] = $create_term['term_id'];
														}
													}
												}
											}
											if ( !empty( $selected_cats ) )  {
												wp_set_post_terms( $id, $selected_cats, SF_Taxonomies::JOB_TYPE_TAXONOMY );	
											}
										}

									}
									
								} elseif ( $_POST['listing_type'] == SF_Facility::POST_TYPE ) { //Create Facility
									
									$post = array(
										'post_title' => $data['company'],
										'post_name' => sanitize_title($data['company']),
										'post_status' => 'publish',
										'post_type' => SF_Facility::POST_TYPE,
									);
								
									$id = wp_insert_post( $post );
									if ( !is_wp_error( $id ) ) {
										
										SF_Facility::save_field($id, 'email', $email_address);
										SF_Facility::save_field($id, 'name', $data['name']);
										SF_Facility::save_field($id, 'phone', $data['phone']);
										SF_Facility::save_field($id, 'website', $data['website']);
										SF_Facility::save_field($id, 'company', $data['company']);
										SF_Facility::save_field($id, 'location', $data['location']);
										SF_Facility::save_field($id, 'location_zip', $data['location_zip']);
										
										if ( !empty( $data['taxonomy_type'] ) ) {
											$selected_cats = array();
											foreach ( $data['taxonomy_type'] as $each_term ) {
												$each_term = trim($each_term);
												if ( !empty($each_term)) {
													$existing_term = get_term_by('name', $each_term, SF_Taxonomies::JOB_TYPE_TAXONOMY );
													if ( $existing_term && isset($existing_term->term_id) ) {
														$selected_cats[] = $existing_term->term_id;
													} else {
														$create_term = wp_insert_term($each_term, SF_Taxonomies::JOB_TYPE_TAXONOMY);
														if ( isset($create_term['term_id']) ) {
															$selected_cats[] = $create_term['term_id'];
														}
													}
												}
											}
											if ( !empty( $selected_cats ) )  {
												wp_set_post_terms( $id, $selected_cats, SF_Taxonomies::JOB_TYPE_TAXONOMY );	
											}
										}

									}
								}
						
							} else { //end if test mode
								 if ( $_POST['import_mode'] == 'test' ) {
									 $id = 'test';
								 }
							}
							
							if ( $id ) {
								//Record entry
								$log_entries[] = array('email' => $email_address, 'listing_id' => $id);
								$log_entries_count++;
								
								$arrays_duplicate_record_stop[] = $email_address; //Avoid processing duplicates
								
								//Send invite message
								if ( $_POST['import_mode'] == 'import' ) {
									 self::send_website_invite_message($id, $email_address);
								} 
								
							} else {
								if (self::DEBUG) error_log("Did NOT Save Email: ". $email_address . " - post not created for id: ".$id );	
								$log_skipped_invalid_count++;
								
							}
							
					   } else { //Check if already exists
						   $log_skipped_already_exists_count++;
						   $log_skipped_already_exists_array[] = $email_address;
					   }
				   
				   
				   } else { //End if we've already done this order
				   
				   		//Do nothing. we've already imported the data from this order during this import (duplicate email)
				   }
					
			   } else { //End check if valid
			   		$log_skipped_invalid_count++;
			   }
			   
			} //end foreach stock item
			
			//If we were testing, display test message
			if ($_POST['import_mode'] == 'test') {
				self::$error_message .= "<div><strong>TEST MODE: Import mode is set to TEST. No imports or changes to database will be made.</strong></div>";
			}
			
			//Show message
			if ($log_entries_count) {
				$add_entries_msg = '';
				foreach( $log_entries as $entry ) {
					$add_entries_msg .= 'Created Listing ID: <a target="_blank" href="'.get_edit_post_link($entry['listing_id']).'">'.$entry['listing_id'].'</a> with Email: '.$entry['email'].', ';
				}
				self::$success_message .= "<div><strong>IMPORT SUCCESS: Successfully imported ( ".$log_entries_count." ) records:</strong> ".$add_entries_msg."</div>";
								
			} else {
				self::$error_message .= "<div><strong>IMPORT NOTICE:</strong> Data was processed but no imports were made. Check the CSV file you provided and ensure the Columns are correctly named for Imports.</div>";
				
			}
			
			//Check if we skipped any
			if ($log_skipped_invalid_count > 0) {
				self::$error_message .= "<div><strong>IMPORT NOTICE:</strong> Skipped ( ".$log_skipped_invalid_count." ) invalid records.</div>";
			}
			//Check if we skipped any non existing products
			if ($log_skipped_already_exists_count > 0) {
				$add_notexists_msg = '';
				if ( is_array($log_skipped_already_exists_array) && sizeof($log_skipped_already_exists_array) ) {
					$add_notexists_msg .= '<br>';
					foreach( $log_skipped_already_exists_array as $entry) {
						$add_notexists_msg .= $entry.', ';
					}
				}
				self::$error_message .= "<div><strong>IMPORT NOTICE:</strong> Skipped ( ".$log_skipped_already_exists_count." ) already existing records: ".$add_notexists_msg."</div>";
			}
			
			
		} else {
			//determine error
			if ( empty($file_path) || !file_exists($file_path) ) {
				self::$error_message .= "<div><strong>IMPORT ERROR:</strong> No Results - Uploaded File is not accessible.</div>";
			} elseif ($csv) {
				self::$error_message .= "<div><strong>IMPORT ERROR:</strong> No Results - Data is invalid.</div>";
			} else {
				self::$error_message .= "<div><strong>IMPORT ERROR:</strong> No Results.</div>";
			}
				
		}
		
	}
	
	//Convert CSV results to multidimensional array 
	public static function convert_csv_results_to_array($data) {	
		$i = 0;
		$header = array();
		foreach($data as $csvLine){ 
			if (empty($header)) {
				
				foreach ($csvLine as $header_line) {
					$header[] = trim($header_line);
				} 
				
			} else { 
				
				for($n = 0, $m = count($header); $n < $m; $n++){ 
					$key = $header[$n];
					$array[$i][$key] = $csvLine[$n]; 
				} 
				$i++;
			} 
		} 
		//error_log('multidim');
		//error_log(print_r($array, true));
		return $array;
	}
	
	//PHP function str_getcsv, if not exists then write this one (for < PHP 5.3)
    public static function custom_str_getcsv($input, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n') { 
        if (is_string($input) && !empty($input)) { 
            $output = array(); 
            $tmp    = preg_split("/".$eol."/",$input); 
            if (is_array($tmp) && !empty($tmp)) { 
                while (list($line_num, $line) = each($tmp)) { 
                    if (preg_match("/".$escape.$enclosure."/",$line)) { 
                        while ($strlen = strlen($line)) { 
                            $pos_delimiter       = strpos($line,$delimiter); 
                            $pos_enclosure_start = strpos($line,$enclosure); 
                            if ( 
                                is_int($pos_delimiter) && is_int($pos_enclosure_start) 
                                && ($pos_enclosure_start < $pos_delimiter) 
                                ) { 
                                $enclosed_str = substr($line,1); 
                                $pos_enclosure_end = strpos($enclosed_str,$enclosure); 
                                $enclosed_str = substr($enclosed_str,0,$pos_enclosure_end); 
                                $output[$line_num][] = $enclosed_str; 
                                $offset = $pos_enclosure_end+3; 
                            } else { 
                                if (empty($pos_delimiter) && empty($pos_enclosure_start)) { 
                                    $output[$line_num][] = substr($line,0); 
                                    $offset = strlen($line); 
                                } else { 
                                    $output[$line_num][] = substr($line,0,$pos_delimiter); 
                                    $offset = ( 
                                                !empty($pos_enclosure_start) 
                                                && ($pos_enclosure_start < $pos_delimiter) 
                                                ) 
                                                ?$pos_enclosure_start 
                                                :$pos_delimiter+1; 
                                } 
                            } 
                            $line = substr($line,$offset); 
                        } 
                    } else { 
                        $line = preg_split("/".$delimiter."/",$line); 
    
                        /* 
                         * Validating against pesky extra line breaks creating false rows. 
                         */ 
                        if (is_array($line) && !empty($line[0])) { 
                            $output[$line_num] = $line; 
                        }  
                    } 
                } 
                return $output; 
            } else { 
                return false; 
            } 
        } else { 
            return false; 
        } 
    } 

	
	//Handle import data
	public static function handle_import() {
		
		if ( self::on_import_page() && $_POST['import_started'] == 'yes' ) {
			
			//First handle product import
			if ($_FILES["file_import"]["tmp_name"]) {
				self::import_csv( $_FILES["file_import"]["tmp_name"] );
			} else {
				self::$success_message .= "<div><strong>IMPORT NOTICE:</strong> No import file specified, so we are skipping this step.</div>";	
			}
		
		} 
	}
	
	public static function import_success_display() {
		if (self::$success_message) echo '<div id="import_success" class="updated fade"><p>' . self::$success_message . '</p></div>';
	}
	
	public static function import_error_display() {
		if (self::$error_message) echo '<div id="import_error" class="error fade"><p>' . self::$error_message . '</p></div>';
	}
	
	private static function on_import_page() {
		global $pagenow;
		if ($pagenow == 'admin.php' && $_GET['page'] == 'sf-fv-import-page') {
			return true;
		} else {
			return false;
		}
	}
	
	public function view_import_page() { 
		?>
        <div class="wrap">
        <h2>Facilty & Contractor Import</h2>
        <div id="loading_records_notice" class="updated fade" style="display: none;"><p> &nbsp; Uploading file... Please wait. Depending on the size of the file, this might take a while. &nbsp; </p></div>
        <?php 
        
        //Display Errors (if any)
        self::import_error_display(); 
        
        //Display Successes (if any)
        self::import_success_display();
        
        ?>
        <form id="form-sf-import" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>" enctype="multipart/form-data">
         
        <table class="wp-list-table widefat" style="width: auto;"> 
            <thead> 
                <tr> 
                    <th colspan="2" class="row-title"><strong>Import Listings CSV</strong></th> 
                </tr> 
            </thead> 
            <tbody> 
            
                <tr> 
                    <td align="right"><label for="file_import"><strong>CSV Import File</strong></label></td>
                    <td align="left">
                        <input type="file" name="file_import" id="file_import">
                    </td> 
                </tr>
                <tr> 
                    <td align="right"><label><strong>Listing Type</strong></label></td>
                    <td align="left">
                        <label for="listing_type_facility"><input type="radio" name="listing_type" id="listing_type_facility" <?php if ($_POST['listing_type'] == SF_Facility::POST_TYPE || $_POST['listing_type'] == '') echo 'checked="checked"'; ?> value="<?php echo SF_Facility::POST_TYPE; ?>"> Facilities</label> &nbsp; &nbsp; <label for="listing_type_contractor"><input type="radio" name="listing_type" id="listing_type_contractor" <?php if ($_POST['listing_type'] == SF_Contractor::POST_TYPE) echo 'checked="checked"'; ?> value="<?php echo SF_Contractor::POST_TYPE; ?>"> Contractors</label> &nbsp;
                    </td>  
                </tr>
                <tr> 
                    <td align="right"><label><strong>Mode</strong></label></td>
                    <td align="left">
                        <label for="import_mode_test"><input type="radio" name="import_mode" id="import_mode_test" <?php if ($_POST['import_mode'] == 'test' || $_POST['import_mode'] == '') echo 'checked="checked"'; ?> value="test"> Test Only</label> &nbsp; &nbsp; 
                        <label for="import_mode_import"><input type="radio" name="import_mode" id="import_mode_import" <?php if ($_POST['import_mode'] == 'import') echo 'checked="checked"'; ?>  value="import"> Import</label> &nbsp;
                    </td>  
                </tr>
                <tr> 
                    <td align="right">&nbsp; </td>
                    <td align="left"><input type="hidden" id="import_started" name="import_started" value="yes" />
                    <input type="submit" name="do-import-submit" id="do-import-submit" class="button-primary" value="Proceed">
                    </td>
                 </tr>
             
            </tbody> 
        </table>
        <p><br>
         <em>Note: Import will look for existing listings by email address. If the email address exists, the listing will not be added.</em>
         <br><em>Import Column names must match (including case): Name, Company, City, State, Zip, Email, Phone, Industry, Type</em>
        </p>
        <p><br>---<br>Developed by: <a target="_blank" href="http://www.studiofidelis.com">Studio Fidelis</a></p>
        </form>
        </div><!-- end .wrap -->
        <script type="text/javascript">
            var $j = jQuery.noConflict();
            $j(function() {
                //import button clicked
                $j('#do-import-submit').click(function() {
                    //Hide error message (if any)
                    if ($j('#import_error').length > 0) {
                        $j('#import_error').fadeOut('slow');
                    }
                    if ($j('#import_success').length > 0) {
                        $j('#import_success').fadeOut('slow');
                    }
                    $j('#loading_records_notice').fadeIn('slow');
                    
                });
            });
            
            //On page load, hide the loading Notice
            /*
            $j(document).ready(function(){
                $j("#loading_records_notice").fadeOut('slow');
            });
            */
            
        </script>
        
        <?php
	}  
  
	public function sf_fv_import_admin_actions() {  
		if (current_user_can('administrator')) {
			add_menu_page( "FV Import", "FV Import", "manage_options", "sf-fv-import-page", array( get_class(), 'view_import_page') );
		}
	}
}
