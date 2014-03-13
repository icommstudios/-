<?php

class SF_Project extends SF_FV {
	
	const POST_TYPE = 'fv_project';
	const REWRITE_SLUG = 'project';
	
	const CACHE_PREFIX = 'fv_project';
	const CACHE_GROUP = 'fv_project';

	protected $user_id;
	private static $instances;

	private static $meta_keys = array(
		'budget' => '_budget', // string
		'deadline' => '_deadline', // string
		'location' => '_location', // string
		'location_zip' => '_location_zip', // string
		'facility_id' => '_facility_id', //string
		'contractor_id' => '_contractor_id', //string
		'proposal_id' => '_proposal_id', //string
		'endorsement_id_by_contractor' => '_endorsement_id_by_contractor', //string
		'endorsement_id_by_facility' => '_endorsement_id_by_facility', //string
		'user_id' => '_user_id', //string
		'invited_contractors' => '_invited_contractors', //array (multiple)
		'invited_contractors_accepted' => '_invited_contractors_accepted', //array (multiple)
		'project_step' => '_project_step', //string
	);

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_project_type') );
		
		// Meta Boxes
		add_action( 'add_meta_boxes', array(get_class(), 'add_meta_boxes'));
		add_action( 'save_post', array( get_class(), 'save_meta_boxes' ), 10, 2 );
		
		//Caches
		add_action( 'update_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );
		add_action( 'added_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );
		add_action( 'delete_post_meta', array( get_class(), 'maybe_clear_id_cache' ), 10, 4 );
		
		//Require login
		add_action( 'template_redirect', array( get_class(), 'require_login_to_view_single'), 20 ); 

	}
	
	public static function require_login_to_view_single() {
		if ( is_singular(self::POST_TYPE) && !is_user_logged_in() ) {
			//Show login message
			self::set_message( self::__('Please login to view this page.'), self::MESSAGE_STATUS_ERROR );
			
			//Redirect to login
			$redirect_str = str_replace( home_url(), '', $_SERVER['REQUEST_URI'] ); 
			$url = add_query_arg( 'redirect_to', $redirect_str, home_url(SF_Users::LOGIN_PATH) );
			wp_redirect( $url );
			exit();
		}
	}

	public static function register_project_type() {
		
		 $labels = array(
			'name'               => 'Project',
			'singular_name'      => 'Project',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Project',
			'edit_item'          => 'Edit Project',
			'new_item'           => 'New Project',
			'all_items'          => 'All Projects',
			'view_item'          => 'View Project',
			'search_items'       => 'Search Projects',
			'not_found'          => 'No Projects found',
			'not_found_in_trash' => 'No Projects found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Projects'
		  );
		
		  $args = array(
			'labels'             => $labels,
			'public'             => true,
			'publicly_queryable' => true,
			'show_ui'            => true,
			'show_in_menu'       => true,
			'query_var'          => true,
			'rewrite'            => array( 'slug' => self::REWRITE_SLUG, 'with_front' => TRUE ),
			'capability_type'    => 'post',
			'has_archive'        => true,
			'hierarchical'       => false,
			'menu_position'      => null,
			'supports'           => array( 'title', 'editor', 'thumbnail', 'excerpt', 'comments' ),
			//'menu_icon' 		 => SF_FV_URL."/assets/charity-icon.png"
		  );
		
		 register_post_type( self::POST_TYPE, $args );
		
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get the project instance for the user
	 */
	public static function get_instance( $id = 0 ) {
		if ( !$id )
			return NULL;
		
		if ( !isset( self::$instances[$id] ) || !self::$instances[$id] instanceof self )
			self::$instances[$id] = new self( $id );

		if ( !isset( self::$instances[$id]->post->post_type ) )
			return NULL;
		
		if ( self::$instances[$id]->post->post_type != self::POST_TYPE )
			return NULL;
		
		return self::$instances[$id];
	}
	
	/**
	 * Get the project ids for the user
	 */
	// Note: returns multiple ids
	public static function get_project_ids_for_user( $user_id = 0 ) {
		if ( $user_id == -1 ) { // anonymous or temp id
			return FALSE;
		}
		if ( !is_numeric( $user_id ) ) {
			$user_id = 0; // not valid user id, so error
		}
		if ( !$user_id ) {
			$user_id = (int)get_current_user_id();
		}
		// first lookup cache cache
		if ( $project_id = self::get_id_cache( $user_id ) ) {
			return $project_id;
		}
		$project_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );

		if ( empty( $project_ids ) ) {
			return FALSE;
		} else {
			$project_id = $project_ids[0];
		}

		self::set_id_cache( $user_id, $project_id );

		return $project_id;
	}
	
	/**
	 * Get the project ids for the facility
	 */
	// Note: returns multiple ids
	public static function get_project_ids_for_facility( $facility_id = 0 ) {
		
		if ( !is_numeric( $facility_id ) ) {
			$facility_id = 0; // not valid user id, so error
			return FALSE;
		}
		
		$project_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['facility_id'] => $facility_id ) );

		if ( empty( $project_ids ) ) {
			return FALSE;
		}

		return $project_ids;
	}
	/**
	 * Get the project ids for the contractor
	 */
	// Note: returns multiple ids
	public static function get_project_ids_for_contractor( $contractor_id = 0 ) {
		
		if ( !is_numeric( $contractor_id ) ) {
			$facility_id = 0; // not valid user id, so error
			return FALSE;
		}
		
		$project_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['contractor_id'] => $contractor_id ) );

		if ( empty( $project_ids ) ) {
			return FALSE;
		}

		return $project_ids;
	}

	public static function get_user_id_for_project( $project_id ) {
		if ( isset( self::$instances[$project_id] ) ) {
			return self::$instances[$project_id]->user_id;
		} else {
			$user_id = get_post_meta( $project_id, self::$meta_keys['user_id'], TRUE );
			return $user_id;
		}
	}
	
	public static function get_facility_id_for_project( $project_id ) {
		if ( isset( self::$instances[$project_id] ) ) {
			return self::$instances[$project_id]->facility_id;
		} else {
			$user_id = get_post_meta( $project_id, self::$meta_keys['facility_id'], TRUE );
			return $user_id;
		}
	}
	
	public static function get_contractor_id_for_project( $project_id ) {
		if ( isset( self::$instances[$project_id] ) ) {
			return self::$instances[$project_id]->contractor_id;
		} else {
			$user_id = get_post_meta( $project_id, self::$meta_keys['contractor_id'], TRUE );
			return $user_id;
		}
	}

	/**
	 * Create a new project
	 * Note: Returns wp_error if wp_insert_post fails
	 */
	public static function new_project( $user_id, $facility_id = '', $title = '') {
		if ( empty( $title ) &&  $facility_id ) {
			$title = get_the_title($facility_id).self::__( ' Project' );
		}
		if ( empty($title) && $user_id ) {
			$user = get_userdata( $user_id );
			if ( !is_a( $user, 'WP_User' ) ) { // prevent an project being created without a proper user_id associated.
				return NULL;
			}
			$title = $user->user_login;
			if ( $user->user_nicename ) {
				$title = $user->user_nicename;
			}
			
		}
		if ( $title ) {
			$post = array(
				'post_title' => $title,
				'post_name' => sanitize_title($title),
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
			);
		} else { // create a post with generated title
			$post = array(
				'post_title' => self::__( 'Project' ).' '.$_SERVER['REMOTE_ADDR'],
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
			);
		}
		$id = wp_insert_post( $post );
		if ( !is_wp_error( $id ) ) {
			update_post_meta( $id, self::$meta_keys['user_id'], $user_id );
			//IMPORTANT - Set values (even if blank), so that meta_queries can find posts with blank values
			update_post_meta( $id, self::$meta_keys['facility_id'], $facility_id );
			update_post_meta( $id, self::$meta_keys['contractor_id'], 0 );
			update_post_meta( $id, self::$meta_keys['proposal_id'], 0 );
			update_post_meta( $id, self::$meta_keys['endorsement_id_by_contractor'], 0 );
			update_post_meta( $id, self::$meta_keys['endorsement_id_by_facility'], 0 );
			update_post_meta( $id, self::$meta_keys['project_step'], 0 );
			
		}
		return $id;
	}



	/**
	 * When the user_id post meta for an project is updated, flush the cache for old and new
	 * Hooked into wp actions:
	 * update_post_meta (update_{$meta_type}_meta)
	 * added_post_meta (added_{$meta_type}_meta)
	 * delete_post_meta (delete_{$meta_type}_meta)
	 */
	public static function maybe_clear_id_cache( $meta_id, $object_id, $meta_key, $meta_value ) {
		if ( $meta_key == self::$meta_keys['user_id'] && get_post_type($object_id) == self::POST_TYPE ) {
			if ( $meta_value ) { // this might be empty on a delete_post_meta
				self::clear_id_cache($meta_value);
			}
			$old_value = get_post_meta($object_id, $meta_key);
			if ( $old_value != $meta_value ) { // this is probably the same on added_post_meta
				self::clear_id_cache($old_value);
			}
		}
	}

	/**
	 * Get the project ID cache for the user or facility
	 */
	private static function get_id_cache( $id ) {
		return (int)wp_cache_get(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * Set the project ID cache for the user or facility
	 */
	private static function set_id_cache( $id, $project_id ) {
		wp_cache_set(self::CACHE_PREFIX.'_id_'.$id, (int)$project_id, self::CACHE_GROUP);
	}

	/**
	 * Delete the project ID cache for the user or facility
	 */
	private static function clear_id_cache( $id ) {
		wp_cache_delete(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * If the current query is for the project post type
	 */
	public static function is_project_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	
	/**
	 * Get all project posts
	 */
	public static function get_projects() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$projects = new WP_Query( $args );
		return $projects->posts;
	}
	
	
	/**
	 * Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('fv_project_fields', 'Project', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'normal', 'default');
		add_meta_box('fv_project_status', 'Status', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'side', 'high');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'fv_project_fields':
				self::show_project_meta_box($post, $metabox);
				break;
			case 'fv_project_status':
				self::show_status_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}
	
	private static function show_status_meta_box( $post, $metabox ) {
		$user_id = self::get_field($post->ID, 'user_id');
		
		$project_step = self::get_field($post->ID, 'project_step');
		
		$facility_id = self::get_field($post->ID, 'facility_id');
		$facility_title = ($facility_id) ? get_the_title($facility_id).' (ID: '.$facility_id.')' : __( 'Empty' );
		
		$contractor_id = self::get_field($post->ID, 'contractor_id');
		$contractor_title = ($contractor_id) ? get_the_title($contractor_id).' (ID: '.$contractor_id.')' : __( 'Not Assigned to a Contractor' );
		
		$proposal_id = self::get_field($post->ID, 'proposal_id');
		$proposal_title = ($proposal_id) ? 'Proposal ID: '.$proposal_id : __( 'None' );
		?>
        <input type="hidden" name="<?php echo self::$meta_keys['user_id'] ?>" id="<?php echo self::$meta_keys['user_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'user_id'))); ?>">
        <input type="hidden" name="<?php echo self::$meta_keys['project_step'] ?>" id="<?php echo self::$meta_keys['project_step'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'project_step'))); ?>">
        <p>
            <label><?php _e( 'Facility:' ); ?>  <strong><?php echo $facility_title; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['facility_id'] ?>" id="<?php echo self::$meta_keys['facility_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'facility_id'))); ?>">
        </p>
        <p>
            <label><?php _e( 'Contractor:' ); ?>  <strong><?php echo $contractor_title; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['contractor_id'] ?>" id="<?php echo self::$meta_keys['contractor_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'contractor_id'))); ?>">
        </p>
        <p>
            <label><?php _e( 'Accepted Proposal:' ); ?>  <strong><?php echo $proposal_title; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['proposal_id'] ?>" id="<?php echo self::$meta_keys['proposal_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'proposal_id'))); ?>">
        </p>
        <?php
	}
	
	private static function show_project_meta_box( $post, $metabox ) {
		
		?>
       	<p>
            <label for="<?php echo self::$meta_keys['location'] ?>"><?php _e('Location (city, state)'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['location'] ?>" id="<?php echo self::$meta_keys['location'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'location'))); ?>">
        </p>
         <p>
            <label for="<?php echo self::$meta_keys['location_zip'] ?>"><?php _e('Location Zip Code'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['location_zip'] ?>" id="<?php echo self::$meta_keys['location_zip'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'location_zip'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['budget'] ?>"><?php _e('Budget'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['budget'] ?>" id="<?php echo self::$meta_keys['budget'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'budget'))); ?>"><em>type numbers only, no currency sign ( $ )</em>
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['deadline'] ?>"><?php _e('Deadline'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['deadline'] ?>" id="<?php echo self::$meta_keys['deadline'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'deadline'))); ?>">
        </p>  
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
	
		// only continue if it's an project post
		if ( $post->post_type != self::POST_TYPE ) {
			return;
		}
		// don't do anything on autosave, auto-draft, bulk edit, or quick edit
		if ( wp_is_post_autosave( $post_id ) || $post->post_status == 'auto-draft' || defined('DOING_AJAX') || isset($_GET['bulk_edit']) ) {
			return;
		}
		if (empty($_POST)) {
			return;	
		}
		
		self::save_meta_box($post_id, $post);
	}

	private static function save_meta_box( $post_id, $post ) {
		//Save all post form fields
		if ( isset( $_POST[self::$meta_keys['location']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location'], stripslashes($_POST[self::$meta_keys['location']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location_zip']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location_zip'], stripslashes($_POST[self::$meta_keys['location_zip']]) );
		}
		if ( isset( $_POST[self::$meta_keys['budget']] ) ) {
			update_post_meta($post_id, self::$meta_keys['budget'], stripslashes($_POST[self::$meta_keys['budget']]) );
		}
		if ( isset( $_POST[self::$meta_keys['deadline']] ) ) {
			update_post_meta($post_id, self::$meta_keys['deadline'], stripslashes($_POST[self::$meta_keys['deadline']]) );
		}
		//hidden fields
		if ( isset( $_POST[self::$meta_keys['contractor_id']] ) ) {
			update_post_meta($post_id, self::$meta_keys['contractor_id'], stripslashes($_POST[self::$meta_keys['contractor_id']]) );
		}
		if ( isset( $_POST[self::$meta_keys['proposal_id']] ) ) {
			update_post_meta($post_id, self::$meta_keys['proposal_id'], stripslashes($_POST[self::$meta_keys['proposal_id']]) );
		}
		if ( isset( $_POST[self::$meta_keys['project_step']] ) ) {
			update_post_meta($post_id, self::$meta_keys['project_step'], stripslashes($_POST[self::$meta_keys['project_step']]) );
		}
	}
	
	public static function get_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key) {
			$value  = update_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function save_field_multiple( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = add_post_meta( $post_id, self::$meta_keys[$meta_key], $new_value );
		}
		return $value;
	}
	
	public static function get_field_multiple( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], false );
		}
		return $value;
	}
	
	/* Template / Public functions */
	
	public static function field_keys() {
		return self::$meta_keys;
	}
	
	public static function load_form_fields($post_id) {
		$fields = array();
		
		//Load post content and title
		if ( $post_id && !isset( $_POST['post_content'] ) ) {
			$fields['post_content'] = get_post_field('post_content', $post_id, 'raw');
		} else {
			$fields['post_content'] = stripslashes($_POST['post_content']) ;
			
		}
		if ( $post_id && !isset( $_POST['post_title'] ) ) {
			$fields['post_title'] = get_the_title( $post_id );
		} else {
			$fields['post_title'] = stripslashes($_POST['post_title']) ;
		}
		
		//Load fields
		foreach ( self::$meta_keys as $meta_key_name => $meta_key_value ) {
			if ( $post_id && !isset( $_POST[$meta_key_value] ) ) {
				$fields[$meta_key_name] = get_post_meta($post_id, $meta_key_value, TRUE);
			} else {
				$fields[$meta_key_name] = stripslashes($_POST[$meta_key_value]);
			}
		}
		//Load taxonomy fields
		if ( $post_id && !isset( $_POST[SF_Taxonomies::JOB_TYPE_TAXONOMY] ) ) {
			//tax type is category so load ids
			$fields[SF_Taxonomies::JOB_TYPE_TAXONOMY] = wp_get_object_terms( $post_id, SF_Taxonomies::JOB_TYPE_TAXONOMY, array( 'fields' => 'ids' ) );
		} else {
			$fields[SF_Taxonomies::JOB_TYPE_TAXONOMY] = $_POST[SF_Taxonomies::JOB_TYPE_TAXONOMY];
		}
	
		return $fields;
	}
	
	public static function save_form_fields($post_id) {
		
		//Save post content
		$update_post = '';
		if ( isset( $_POST['post_content'] ) ) {
			$allowed_tags = wp_kses_allowed_html( 'post' );
			$_POST['post_content'] = wp_kses( $_POST['post_content'], $allowed_tags );
			$update_post['post_content'] = $_POST['post_content'];
		}
		if ( isset( $_POST['post_title'] ) ) {
			$update_post['post_title'] = $_POST['post_title'];
		}
		if ( $update_post ) {
			global $wpdb;
			$update_post = stripslashes_deep( $update_post );
			$wpdb->update( $wpdb->posts, $update_post, array( 'ID' => $post_id ) );	
		}
		
		if ( isset( $_POST[self::$meta_keys['location']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location'], stripslashes($_POST[self::$meta_keys['location']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location_zip']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location_zip'], stripslashes($_POST[self::$meta_keys['location_zip']]) );
		}
		if ( isset( $_POST[self::$meta_keys['budget']] ) ) {
			update_post_meta($post_id, self::$meta_keys['budget'], stripslashes($_POST[self::$meta_keys['budget']]) );
		}
		if ( isset( $_POST[self::$meta_keys['deadline']] ) ) {
			update_post_meta($post_id, self::$meta_keys['deadline'], stripslashes($_POST[self::$meta_keys['deadline']]) );
		}
		
		//Save taxonomy
		if ( isset( $_POST[SF_Taxonomies::JOB_TYPE_TAXONOMY] ) ) {
			//Filter posted categories for amount allowed based on membership
			$project_facility_id = self::get_field($post_id, 'facility_id');
			$allowed_categories = fv_get_facility_membership_addon_categories($project_facility_id);
			$selected_cats = $_POST[SF_Taxonomies::JOB_TYPE_TAXONOMY];
			if ( !empty($selected_cats) && sizeof($selected_cats) > $allowed_categories) {
				$cat_ii = 1;
				foreach ($selected_cats as $cat_key => $cat ) {
					if ( $cat_ii > $allowed_categories ) {
						unset($selected_cats[$cat_key]);
					}
					$cat_ii++; //increase category count
				}
			}
			wp_set_post_terms( $post_id, $selected_cats, SF_Taxonomies::JOB_TYPE_TAXONOMY );
		}
		
	}
	
	public static function set_project_contractor( $project_id, $contractor_id ) {
		if ( !$project_id || !$contractor_id ) return array('result' => false, 'message' => 'missingvalues' ); //missing values
		
		$current_contractor = get_post_meta( $project_id, self::$meta_keys['contractor_id'], true );
		if ( !$current_contractor ) {
			//Check contractor
			if ( get_post_type($contractor_id) == SF_Contractor::POST_TYPE ) {
				$result = update_post_meta($project_id, self::$meta_keys['contractor_id'], $contractor_id );
				if ( $result ) {
					return array('result' => true, 'message' => 'success' ); //success
				}
			} else {
				return array('result' => false, 'message' => 'invalidtype' ); //invalid contractor 
			}
		} else {
			return array('result' => false, 'message' => 'assigned' ); //already assigned to contractor
		}
		return array('result' => false, 'message' => 'invalid' ); //invalid
	}
	
	public static function set_project_proposal( $project_id, $proposal_id ) {
		if ( !$project_id || !$proposal_id ) return array('result' => false, 'message' => 'missingvalues' ); //missing values
		
		$current_proposal = get_post_meta( $project_id, self::$meta_keys['proposal_id'], true );
		if ( !$current_proposal ) {
			//Check proposal
			if ( get_post_type($proposal_id) == SF_Proposal::POST_TYPE ) {
				//Get contractor for proposal
				$contractor_id = SF_Proposal::get_contractor_id_for_proposal($proposal_id);
				if ( $contractor_id && get_post_type($proposal_id) == SF_Proposal::POST_TYPE ) {
					//Save the proposal and contractor on this project
					$result1 = self::save_field($project_id, 'contractor_id', $contractor_id );
					$result2 = self::save_field($project_id, 'proposal_id', $proposal_id );
					if ( $result1 && $result2 ) {
						$result3 = SF_Proposal::save_field($proposal_id, 'proposal_status', 'accepted');
						return array('result' => true, 'message' => 'success' ); //success
					} else {
						return array('result' => false, 'message' => 'invalid' ); //invalid
					}
				} else {
					return array('result' => false, 'message' => 'invalidcontractor' ); //invalid contractor
				}
				
			} else {
				return array('result' => false, 'message' => 'invalidtype' ); //invalid type of post 
			}
		} else {
			return array('result' => false, 'message' => 'assigned' ); //already assigned 
		}
		return array('result' => false, 'message' => 'invalid' ); //invalid
	}
	
	public static function load_attachments($post_id, $include_featured = false) {
		//Load photos - attachments to this post
		if ( !$include_featured ) {
			$featured_thumb_id = get_post_meta( $post_id, '_thumbnail_id', true);	
		}
		$args = array(
			  'post_type' => 'attachment',
			  'post_mime_type' => 'image',
			  'numberposts' => -1,
			  'post_status' => null,
			  'post_parent' => $post_id,
			  'post__not_in' => array($featured_thumb_id),
			  'orderby' => 'date',
			  'order' => 'DESC'
			  );
		
		$attachments = get_posts($args);
		
		return $attachments;	
	}
	
	public static function load_file_attachments($post_id) {
		//Get all photo ides
		$args = array(
		  'post_type' => 'attachment',
		  'post_mime_type' => 'image',
		  'numberposts' => -1,
		  'post_status' => null,
		  'post_parent' => $post_id,
		  'fields' => 'ids'
		  );
		$images = get_posts($args);	  
		//Load all attachments that are not in the list of image type attachments
		$args = array(
			  'post_type' => 'attachment',
			  'numberposts' => -1,
			  'post_status' => null,
			  'post_parent' => $post_id,
			  'post__not_in' => (array)$images,
			  'orderby' => 'date',
			  'order' => 'DESC'
			  );
		
		$attachments = get_posts($args);
		
		return $attachments;	
	}
	
	public static function save_attachment($post_id, $file, $post_data = array(), $set_featured = false) {
		if ( !is_array($post_data) ) $post_data = array(); //ensure the built in post data isn't remove by this not being an array
		
		//require the needed files
		require_once(ABSPATH . "wp-admin" . '/includes/image.php');
		require_once(ABSPATH . "wp-admin" . '/includes/file.php');
		require_once(ABSPATH . "wp-admin" . '/includes/media.php');
			
		//then loop over the files that were sent and store them using  media_handle_upload();
		$error = '';
		if (!empty($_FILES) && !empty($_FILES[$file]['name']) ) {
			
			if ($_FILES[$file]['error'] !== UPLOAD_ERR_OK) {
				$error = self::__("Upload error: ").$_FILES[$file]['error'];
				//die();
			}
			$attach_id = media_handle_upload( $file, $post_id, $post_data );
			
			//If featured
			if ( $attach_id && $set_featured == true) {
				update_post_meta( $post_id, '_thumbnail_id', $attach_id );	
			}
			
		} else {
			$error = self::__("Upload error: No file was selected");
		}
		if ( $error ) {
			return array('result' => false, 'error' => $error);
		} else {
			return array('result' => true, 'attach_id' => $attach_id);
		}
	}

}
