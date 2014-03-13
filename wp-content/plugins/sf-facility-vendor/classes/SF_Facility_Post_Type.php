<?php

class SF_Facility extends SF_FV {
	
	const POST_TYPE = 'fv_facility';
	const REWRITE_SLUG = 'facility';
	
	const CACHE_PREFIX = 'fv_facility';
	const CACHE_GROUP = 'fv_facility';

	protected $user_id;
	private static $instances;

	private static $meta_keys = array(
		'name' => '_name', // string
		'title' => '_title', // string
		'company' => '_company', // string
		'phone' => '_phone', // string
		'email' => '_email', // string
		'location' => '_location', // string
		'location_zip' => '_location_zip', // string
		'criminal_history' => '_criminal_history', // bool
		'hours' => '_hours', // string
		'website' => '_website', // string
		'bbb_url' => '_bbb_url', // string
		'hide_profile' => '_hide_profile', // string
		'user_id' => '_user_id', // string
		'membership_type' => '_membership_type', //string
		'membership_expiration' => '_membership_expiration', //string
		'membership_data' => '_membership_data', //array 
		'membership_addon_data' => '_membership_addon_data', //array (multiple entries)
		'membership_history' => '_membership_history' //array (multiple entries)
	);

	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_facility_type') );
		
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

	public static function register_facility_type() {
		
		 $labels = array(
			'name'               => 'Facility',
			'singular_name'      => 'Facility',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Facility',
			'edit_item'          => 'Edit Facility',
			'new_item'           => 'New Facility',
			'all_items'          => 'All Facilities',
			'view_item'          => 'View Facility',
			'search_items'       => 'Search Facilities',
			'not_found'          => 'No Facilities found',
			'not_found_in_trash' => 'No Facilities found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Facilities'
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
			'supports'           => array( 'title', 'editor', 'thumbnail', 'comments' ),
			//'menu_icon' 		 => SF_FV_URL."/assets/charity-icon.png"
		  );
		
		 register_post_type( self::POST_TYPE, $args );
		
	}


	protected function __construct( $id ) {
		parent::__construct( $id );
	}

	/**
	 * Get the facility instance by id
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
	 * Get the facility id for the user
	 */
	//Note: returns 1 id
	public static function get_facility_id_for_user( $user_id = 0 ) {
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
		if ( $facility_id = self::get_id_cache( $user_id ) ) {
			return $facility_id;
		}
		$facility_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );

		if ( empty( $facility_ids ) ) {
			return FALSE;
		} else {
			$facility_id = $facility_ids[0];
		}

		self::set_id_cache( $user_id, $facility_id );

		return $facility_id;
	}

	public static function get_user_id_for_facility( $facility_id ) {
		if ( isset( self::$instances[$facility_id] ) ) {
			return self::$instances[$facility_id]->user_id;
		} else {
			$user_id = get_post_meta( $facility_id, self::$meta_keys['user_id'], TRUE );
			return $user_id;
		}
	}

	/**
	 * Create a new facility
	 * Note: Returns wp_error if wp_insert_post fails
	 */
	public static function new_facility( $user_id, $title = '', $data = array() ) {
		if ( empty( $title ) && $user_id ) {
			$user = get_userdata( $user_id );
			if ( !is_a( $user, 'WP_User' ) ) { // prevent an facility being created without a proper user_id associated.
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
		} else { // create a dummy project for anonymous users
			$post = array(
				'post_title' => self::__( 'Facility' ).' '.$_SERVER['REMOTE_ADDR'],
				'post_status' => 'publish',
				'post_type' => self::POST_TYPE,
			);
		}
		$id = wp_insert_post( $post );
		if ( !is_wp_error( $id ) ) {
			update_post_meta( $id, self::$meta_keys['user_id'], $user_id );
			if ( !empty($data['company']) ) {
				update_post_meta( $id, self::$meta_keys['company'], $data['company'] );
			}
			if ( !empty($data['phone']) ) {
				update_post_meta( $id, self::$meta_keys['phone'], $data['phone'] );
			}
			if ( !empty($data['website']) ) {
				update_post_meta( $id, self::$meta_keys['website'], $data['website'] );
			}
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
	 * Get the facility ID cache for the user
	 */
	private static function get_id_cache( $id ) {
		return (int)wp_cache_get(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * Set the facility ID cache for the user
	 */
	private static function set_id_cache( $id, $facility_id ) {
		wp_cache_set(self::CACHE_PREFIX.'_id_'.$id, (int)$facility_id, self::CACHE_GROUP);
	}

	/**
	 * Delete the facility ID cache for the user
	 */
	private static function clear_id_cache( $id ) {
		wp_cache_delete(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * If the current query is for the facility post type
	 */
	public static function is_facility_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Looup facility by user_id
	 */
	public static function get_facilities_by_facility( $user_id ) {
		$facilities = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );
		return $facilities;
	}

	/**
	 * Get all facility posts
	 */
	public static function get_facilities() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$facilities = new WP_Query( $args );
		return $facilities->posts;
	}
	
	
	/**
	 * Meta boxes
	 */
	
	public static function add_meta_boxes() {
		add_meta_box('fv_profile_fields', 'Profile', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'normal', 'default');
		add_meta_box('fv_user_box', 'User', array(get_class(), 'show_meta_boxes'), self::POST_TYPE, 'side', 'high');
	}

	public static function show_meta_boxes( $post, $metabox ) {
		switch ( $metabox['id'] ) {
			case 'fv_profile_fields':
				self::show_profile_meta_box($post, $metabox);
				break;
			case 'fv_user_box':
				self::show_user_meta_box($post, $metabox);
				break;
			default:
				self::unknown_meta_box($metabox['id']);
				break;
		}
	}
	
	private static function show_user_meta_box( $post, $metabox ) {
		$show_user_id = self::get_field($post->ID, 'user_id');
		if ( !$show_user_id  ) {
			$show_user_id = 'None';
		}
		?>
        <p>
            <label for="<?php echo self::$meta_keys['user_id'] ?>"><?php _e( 'User ID:' ); ?>  <strong><?php echo $show_user_id; ?></strong></label>
            <input type="hidden" name="<?php echo self::$meta_keys['user_id'] ?>" id="<?php echo self::$meta_keys['user_id'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'user_id'))); ?>">
        </p>
        <?php
	}
        

	private static function show_profile_meta_box( $post, $metabox ) {
		
		?>
        <p>
            <label for="<?php echo self::$meta_keys['company'] ?>"><?php _e( 'Company' ); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['company'] ?>" id="<?php echo self::$meta_keys['company'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'company'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['title'] ?>"><?php _e( 'Title' ); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['title'] ?>" id="<?php echo self::$meta_keys['title'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'title'))); ?>">
        </p>
		<p>
            <label for="<?php echo self::$meta_keys['name'] ?>"><?php _e( 'Name' ); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['name'] ?>" id="<?php echo self::$meta_keys['name'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'name'))); ?>">
        </p>
      
        <p>
            <label for="<?php echo self::$meta_keys['phone'] ?>"><?php _e('Contact Phone'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['phone'] ?>" id="<?php echo self::$meta_keys['phone'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'phone'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['email'] ?>"><?php _e('Contact Email'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['email'] ?>" id="<?php echo self::$meta_keys['email'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'email'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['location'] ?>"><?php _e('Location (city, state)'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['location'] ?>" id="<?php echo self::$meta_keys['location'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'location'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['location_zip'] ?>"><?php _e('Location Zip Code (used for search by location)'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['location_zip'] ?>" id="<?php echo self::$meta_keys['location_zip'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'location_zip'))); ?>">
        </p>
         <p>
            <label for="<?php echo self::$meta_keys['hours'] ?>"><?php _e('Hours'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['hours'] ?>" id="<?php echo self::$meta_keys['hours'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'hours'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['website'] ?>"><?php _e('Website'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['website'] ?>" id="<?php echo self::$meta_keys['website'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'website'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['bbb_url'] ?>"><?php _e('Better Business Profile URL'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['bbb_url'] ?>" id="<?php echo self::$meta_keys['bbb_url'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'bbb_url'))); ?>">
        </p>
         <p>
         <?php 
		 $criminal_history = self::get_field($post->ID, 'criminal_history');
		 ?>
         <label for="<?php echo self::$meta_keys['criminal_history'] ?>"><?php _e('Criminal History'); ?></label><br />
         <select name="<?php echo self::$meta_keys['criminal_history'] ?>" placeholder="do you have criminal history?">
            <option value="" <?php echo ($criminal_history == '') ? 'selected="selected"' : ''; ?>>do you have criminal history?</option>
            <option value="yes" <?php echo ($criminal_history == 'yes') ? 'selected="selected"' : ''; ?>>yes</option>
            <option value="no" <?php echo ($criminal_history == 'no') ? 'selected="selected"' : ''; ?>>no</option>
         </select>
        </p>
        <p>
        	<label for="<?php echo self::$meta_keys['hide_profile'] ?>">
        		<input type="checkbox" value="yes" name="<?php echo self::$meta_keys['hide_profile'] ?>" id="<?php echo self::$meta_keys['hide_profile'] ?>" <?php echo ((bool)self::get_field($post->ID, 'hide_profile') == true) ? 'checked="checked"' : ''; ?>> <?php _e('Hide Profile'); ?>
            </label> 
            
        </p>     
		<?php
	}

	public static function save_meta_boxes( $post_id, $post ) {
	
		// only continue if it's an facility post
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
		if ( isset( $_POST[self::$meta_keys['name']] ) ) {
			update_post_meta($post_id, self::$meta_keys['name'], stripslashes($_POST[self::$meta_keys['name']]) );
		}
		if ( isset( $_POST[self::$meta_keys['title']] ) ) {
			update_post_meta($post_id, self::$meta_keys['title'], stripslashes($_POST[self::$meta_keys['title']]) );
		}
		if ( isset( $_POST[self::$meta_keys['company']] ) ) {
			update_post_meta($post_id, self::$meta_keys['company'], stripslashes($_POST[self::$meta_keys['company']]) );
		}
		if ( isset( $_POST[self::$meta_keys['phone']] ) ) {
			update_post_meta($post_id, self::$meta_keys['phone'], stripslashes($_POST[self::$meta_keys['phone']]) );
		}
		if ( isset( $_POST[self::$meta_keys['email']] ) ) {
			update_post_meta($post_id, self::$meta_keys['email'], stripslashes($_POST[self::$meta_keys['email']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location'], stripslashes($_POST[self::$meta_keys['location']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location_zip']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location_zip'], stripslashes($_POST[self::$meta_keys['location_zip']]) );
		}
		if ( isset( $_POST[self::$meta_keys['hours']] ) ) {
			update_post_meta($post_id, self::$meta_keys['hours'], stripslashes($_POST[self::$meta_keys['hours']]) );
		}
		if ( isset( $_POST[self::$meta_keys['website']] ) ) {
			update_post_meta($post_id, self::$meta_keys['website'], stripslashes($_POST[self::$meta_keys['website']]) );
		}
		if ( isset( $_POST[self::$meta_keys['bbb_url']] ) ) {
			update_post_meta($post_id, self::$meta_keys['bbb_url'], stripslashes($_POST[self::$meta_keys['bbb_url']]) );
		}
		if ( isset( $_POST[self::$meta_keys['criminal_history']] ) ) {
			update_post_meta($post_id, self::$meta_keys['criminal_history'], stripslashes($_POST[self::$meta_keys['criminal_history']]) );
		}
		if ( isset( $_POST[self::$meta_keys['hide_profile']] ) ) {
			update_post_meta($post_id, self::$meta_keys['hide_profile'], true);
		} else {
			update_post_meta($post_id, self::$meta_keys['hide_profile'], false);
		}
		if ( isset( $_POST[self::$meta_keys['user_id']] ) ) {
			update_post_meta($post_id, self::$meta_keys['user_id'], stripslashes($_POST[self::$meta_keys['user_id']]));
		}
	}

	public static function get_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = get_post_meta( $post_id, self::$meta_keys[$meta_key], true );
		}
		return $value;
	}
	
	public static function save_field( $post_id, $meta_key = '', $new_value = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
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
	
	public static function delete_field( $post_id, $meta_key = '' ) {
		$value = '';
		if ($post_id && $meta_key && self::$meta_keys[$meta_key]) {
			$value  = delete_post_meta( $post_id, self::$meta_keys[$meta_key] ); //delete all
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
		/*
		if ( $post_id && !isset( $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY] ) ) {
			//tax type is category so load slugs
			$fields[SF_Taxonomies::JOB_SKILL_TAXONOMY] = wp_get_object_terms( $post_id, SF_Taxonomies::JOB_SKILL_TAXONOMY, array( 'fields' => 'slugs' ) );
		} else {
			$fields[SF_Taxonomies::JOB_SKILL_TAXONOMY] = $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY];
		}
		*/
		
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
		
		//Update fields
		if ( isset( $_POST[self::$meta_keys['name']] ) ) {
			update_post_meta($post_id, self::$meta_keys['name'], stripslashes($_POST[self::$meta_keys['name']]) );
		}
		if ( isset( $_POST[self::$meta_keys['title']] ) ) {
			update_post_meta($post_id, self::$meta_keys['title'], stripslashes($_POST[self::$meta_keys['title']]) );
		}
		if ( isset( $_POST[self::$meta_keys['company']] ) ) {
			update_post_meta($post_id, self::$meta_keys['company'], stripslashes($_POST[self::$meta_keys['company']]) );
		}
		if ( isset( $_POST[self::$meta_keys['phone']] ) ) {
			update_post_meta($post_id, self::$meta_keys['phone'], stripslashes($_POST[self::$meta_keys['phone']]) );
		}
		if ( isset( $_POST[self::$meta_keys['email']] ) ) {
			update_post_meta($post_id, self::$meta_keys['email'], stripslashes($_POST[self::$meta_keys['email']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location'], stripslashes($_POST[self::$meta_keys['location']]) );
		}
		if ( isset( $_POST[self::$meta_keys['location_zip']] ) ) {
			update_post_meta($post_id, self::$meta_keys['location_zip'], stripslashes($_POST[self::$meta_keys['location_zip']]) );
		}
		if ( isset( $_POST[self::$meta_keys['hours']] ) ) {
			update_post_meta($post_id, self::$meta_keys['hours'], stripslashes($_POST[self::$meta_keys['hours']]) );
		}
		if ( isset( $_POST[self::$meta_keys['website']] ) ) {
			update_post_meta($post_id, self::$meta_keys['website'], stripslashes($_POST[self::$meta_keys['website']]) );
		}
		if ( isset( $_POST[self::$meta_keys['bbb_url']] ) ) {
			update_post_meta($post_id, self::$meta_keys['bbb_url'], stripslashes($_POST[self::$meta_keys['bbb_url']]) );
		}
		if ( isset( $_POST[self::$meta_keys['criminal_history']] ) ) {
			update_post_meta($post_id, self::$meta_keys['criminal_history'], stripslashes($_POST[self::$meta_keys['criminal_history']]) );
		}
		if ( isset( $_POST[self::$meta_keys['hide_profile']] ) ) {
			update_post_meta($post_id, self::$meta_keys['hide_profile'], true);
		} else {
			update_post_meta($post_id, self::$meta_keys['hide_profile'], false);
		}
		if ( isset( $_POST[self::$meta_keys['user_id']] ) ) {
			update_post_meta($post_id, self::$meta_keys['user_id'], stripslashes($_POST[self::$meta_keys['user_id']]));
		}
		
		//Save taxonomy
		if ( isset( $_POST[SF_Taxonomies::JOB_TYPE_TAXONOMY] ) ) {
			//Filter posted categories for amount allowed based on membership
			$allowed_categories = fv_get_facility_membership_addon_categories($post_id);
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
		/*
		if ( isset( $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY] ) ) {
			wp_set_post_terms( $post_id, $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY], SF_Taxonomies::JOB_SKILL_TAXONOMY );
		}
		*/
		
	}
	
	public static function load_attachments($post_id) {
		//Load photos - attachments to this post
		//Type is image or 
		$featured_thumb_id = get_post_meta( $post_id, '_thumbnail_id', true);	
		$args = array(
			  'post_type' => 'attachment',
			  'post_mime_type' => $type,
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
