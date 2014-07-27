<?php
/*
Plugin Name: Studio Fidelis Dev import Categories
Version: 1.0
Plugin URI: http://studiofidelis.com/
Description: Import Categories
Author: StudioFidelis.com / Daniel Schuring
Author URI: http://studiofidelis.com/
Plugin Author: Daniel Schuring
Plugin Author URI: http://studiofidelis.com/
Domain Path: /lang
*/

define ('SF_DEV_IMPORT_FV_URL', plugins_url( '', __FILE__) );
define( 'SF_DEV_IMPORT_PATH', WP_PLUGIN_DIR . '/' . basename( dirname( __FILE__ ) ) );

abstract class SF_dev_import_wpe_cats {
	
	const DEBUG = TRUE; //Set to FALSE in production environment
	
	private static $error_message;
	private static $success_message;
	
	public static function init() {
	
		if ( is_admin() ) {
			
			//Import
			add_action('admin_menu', array( get_class(), 'sf_dev_cat_import_admin_actions' ) ); //Add Menu
			//Add action to run import - run at wp_loaded to ensure all custom posts, taxonomies are registered
			add_action('wp_loaded', array( get_class(), 'handle_import'), 999 );
		}
		
	}
	
	

	public function insert_term($this_term, $this_parent_term = false) {
		global $cached_created_terms;
		
		$parent = 0;
		if ( $this_parent_term ) {
			
			//if in cached terms
			if ( $cached_created_terms[$this_parent_term] ) {
				$parent_term = $cached_created_terms[$this_parent_term];
			}
			$parent_term = (isset($parent_term->term_id)) ? $parent_term : get_term_by('name', $this_parent_term, 'fv_job_type' );
			//if not exist, now try using sanitized title
			$parent_term = ( $parent_term && isset($parent_term->term_id) ) ? $parent_term : get_term_by('slug', sanitize_title($this_parent_term), 'fv_job_type' );
			if ( $parent_term && isset($parent_term->term_id) ) {
				//success
				$parent = $parent_term->term_id;
				
				$cached_created_terms[$this_parent_term] = $parent_term;
			} else {
				echo '<br>ERROR no existing parent term : '.$this_parent_term;
			}
		}
		
		
		//Check cat
		
		//if in cached terms
		if ( $cached_created_terms[$this_term] ) {
			$existing_term = $cached_created_terms[$this_term];
		}
		$existing_term = (isset($existing_term->term_id)) ? $existing_term : get_term_by('name', $this_term, 'fv_job_type' );
		//if not exist, now try using sanitized title
		$existing_term = ( $existing_term && isset($existing_term->term_id) ) ? $existing_term : get_term_by('slug', sanitize_title($this_term), 'fv_job_type' );
		
		if ( $existing_term && isset($existing_term->term_id) ) {
			//wp_set_post_terms( $id, $existing_term->term_id, 'wpsc_product_category', TRUE );
			
			$cached_created_terms[$this_term] = $existing_term;
			
			return $existing_term;
			
		} else {
			$create_term = wp_insert_term($this_term, 'fv_job_type', array('parent' => $parent));
			if ( !is_wp_error($create_term) && isset($create_term['term_id']) ) {	
				$existing_term = get_term_by('id', $create_term['term_id'], 'fv_job_type' );
				$cached_created_terms[$this_term] = $existing_term;
				return $existing_term;
			} else {
				echo '<br>ERROR creating term "'.$this_term.'" : '.print_r($create_term, true);	
			}
		}
		return false;
	}
	
	//Run import CSV
	public static function import_csv($file_path = NULL) {
		global $cached_created_terms;
		
		$file_path = dirname(__FILE__)."/completevendorcategory.csv";
		
		$file = file_get_contents($file_path );
		
		$csv = false;
		
		//Get data from file
		if ($file) {
			
			$csv = self::custom_str_getcsv($file, $delimiter = ',', $enclosure = '"', $escape = '\\', $eol = '\n');
			/*
			if (!function_exists('str_getcsv')) {  //If not PHP 5.3 +
				$csv = self::str_getcsv(file_get_contents($file_path), ',', '"');
			} else {
				if (self::DEBUG) error_log("str_getcsv");
				$csv = str_getcsv(file_get_contents($file_path), ',', '"');
			}
			*/
			if ($csv) {
				if (self::DEBUG) error_log("convert_csv_results_to_array");
				$csv = self::convert_csv_results_to_array( $csv );
			}
		}
		
		
		
		//Do we have data?
		if ( is_array($csv) && !empty($csv) ) {
			
			//Generate an import Batch ID (stored later)
			$batch_id = 'batch-import-'.time().'-'.mt_rand();
			$batch_timestamp = time();
			
			$log_entries = array();
			$log_entries_count = 0;
			$log_skipped_invalid_count = 0;
			$log_skipped_already_exists_count = 0;
			$log_skipped_already_exists_array = array();
			
			$log_failed_categories_array = array();

			
			//For each
			$arrays_duplicate_record_stop =  array();
			
			$cached_created_terms = array();
			
			//If cleanup
			if ($_GET['cleanup'] ) {
				$toplevelcats = array();
				$level = 1;
				foreach ($csv as $line) {
					
					foreach ( $line as $lk => $l ) {
						$l = trim($l);
						$l = trim($l);
						$line[$lk] = html_entity_decode($l);
					}
					$val = $line['level'.$level];
					if ( !in_array($val, $toplevelcats) ) {
						$toplevelcats[$val] = $val;
					}
					
		
				}
				
				
				$top_categories = get_terms( SF_Taxonomies::JOB_TYPE_TAXONOMY, array(
					'orderby'    => 'name',
					'hide_empty' => 0,
					'parent' => 0
				 ) );
		
				 if ( $top_categories && !is_wp_error($top_categories) ) {
					foreach ( $top_categories as $top_c ) {
						
						if ( strpos($top_c->name, ':') && !in_array($top_c->name, $toplevelcats) ) {
							echo '<br>found unknown cat: '.$top_c->name;
							wp_delete_term( $top_c->term_id, SF_Taxonomies::JOB_TYPE_TAXONOMY );
						}
					}
				 }
				 return;
			}
			
			self::run_import_cat_level($csv, 1);
			self::run_import_cat_level($csv, 2);
			self::run_import_cat_level($csv, 3);
			self::run_import_cat_level($csv, 4);
			
			
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
	
	public function run_import_cat_level($csv, $level) {
		
		if ( $level ) {
				$level = intval($level);
				foreach ($csv as $lineii => $line) {
					
					foreach ( $line as $lk => $l ) {
						$l = trim($l);
						$l = trim($l);
						$line[$lk] = html_entity_decode($l);
					}
					
					$cat = $line['level'.$level];
					if ( !empty($cat) ) {
						
						$parent_level = $level - 1;
						$parent_cat = false;
						if ( $parent_level > 0 ) {
							$topmost_parent_prefix = $line['level1'];
							
							if ( $parent_level > 1 ) {
								$parent_cat = trim($topmost_parent_prefix).': '.$line['level'.$parent_level];
							} else {
								$parent_cat = $line['level'.$parent_level];
							}
							$cat = trim($topmost_parent_prefix).': '.$cat;
						}
						
						if ( !in_array($cat, $arrays_duplicate_record_stop) ) {
							echo '<br>creating cat '.$cat.' with parent cat: '.$parent_cat;
							$results[] = self::insert_term($cat, $parent_cat);
						}
						
						$arrays_duplicate_record_stop[] = $cat;
					}
					
				}
				self::$success_message .= 'Inserted: '.sizeof($results, true );
				//self::$success_message .= '<br><br><pre>';
				
				//self::$success_message .= print_r($results, true );
				//self::$success_message .= '</pre><br><br><br>';
				
				
			} else {
				self::$error_message .= 'Invalid level';
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
			
			// handle import
			self::import_csv( true );
			
		
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
		if ($pagenow == 'admin.php' && $_GET['page'] == 'sf-dev-import-page') {
			return true;
		} else {
			return false;
		}
	}
	
	public function view_import_page() { 
		?>
        <div class="wrap">
        <h2>Import</h2>
        <div id="loading_records_notice" class="updated fade" style="display: none;"><p> &nbsp; Uploading file... Please wait. Depending on the size of the file, this might take a while. &nbsp; </p></div>
        <?php 
        
        //Display Errors (if any)
        self::import_error_display(); 
        
        //Display Successes (if any)
        self::import_success_display();
        
        ?>
        <form id="form-sf-import" method="POST" action="<?php echo $_SERVER['REQUEST_URI']; ?>">
         
        <table class="wp-list-table widefat" style="width: auto;"> 
            <thead> 
                <tr> 
                    <th colspan="2" class="row-title"><strong>Import Cats</strong></th> 
                </tr> 
            </thead> 
            <tbody> 
       
                <tr> 
                    <td align="right"><label><strong>Mode</strong></label></td>
                    <td align="left">
                        <label for="import_mode_test"><input type="radio" name="level" value="1"> Level 1</label> &nbsp; &nbsp; 
                        <label for="import_mode_import"><input type="radio" name="level" value="2"> Level 2</label> &nbsp; 
                        <label for="import_mode_import"><input type="radio" name="level" value="3"> Level 3</label> &nbsp;
                        <label for="import_mode_import"><input type="radio" name="level" value="4"> Level 4</label> &nbsp;
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
  
	public function sf_dev_cat_import_admin_actions() {  
		if (current_user_can('administrator')) {
			add_menu_page( "Dev Import", "Dev Import", "manage_options", "sf-dev-import-page", array( get_class(), 'view_import_page') );
		}
	}
}

add_action('plugins_loaded', 'init_sf_dev_import_wpe_cats');
function init_sf_dev_import_wpe_cats() {
SF_dev_import_wpe_cats::init();
}