<?php

class SF_Contractor extends SF_FV {
	
	const POST_TYPE = 'fv_contractor';
	const REWRITE_SLUG = 'contractor';
	
	const CACHE_PREFIX = 'fv_contractor';
	const CACHE_GROUP = 'fv_contractor';

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
		'years_of_experience' => '_years_of_experience', // string
		'contractor_license' => '_contractor_license', // string
		'insurance_account' => '_insurance_account', // string
		'hide_profile' => '_hide_profile', // string
		'category_data' => '_category_data', //array
		'user_id' => '_user_id', // string
		'membership_type' => '_membership_type', //string
		'membership_expiration' => '_membership_expiration', //string
		'membership_data' => '_membership_data', //array 
		'membership_addon_data' => '_membership_addon_data', //array (multiple entries)
		'membership_history' => '_membership_history' //array (multiple entries)
	);
	
	public static function init() {
		
		// Register Post type
		add_action( 'init', array( get_class(), 'register_contractor_type') );
		
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

	public static function register_contractor_type() {
		
		 $labels = array(
			'name'               => 'Contractor',
			'singular_name'      => 'Contractor',
			'add_new'            => 'Add New',
			'add_new_item'       => 'Add New Contractor',
			'edit_item'          => 'Edit Contractor',
			'new_item'           => 'New Contractor',
			'all_items'          => 'All Contractors',
			'view_item'          => 'View Contractor',
			'search_items'       => 'Search Contractors',
			'not_found'          => 'No Contractors found',
			'not_found_in_trash' => 'No Contractors found in Trash',
			'parent_item_colon'  => '',
			'menu_name'          => 'Contractors'
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
	 * Get the contractor instance by id
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
	 * Get the contractor id for the user
	 */
	//Note: returns 1 id
	public static function get_contractor_id_for_user( $user_id = 0 ) {
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
		if ( $contractor_id = self::get_id_cache( $user_id ) ) {
			return $contractor_id;
		}
		$contractor_ids = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );

		if ( empty( $contractor_ids ) ) {
			return FALSE;
		} else {
			$contractor_id = $contractor_ids[0];
		}

		self::set_id_cache( $user_id, $contractor_id );

		return $contractor_id;
	}

	public static function get_user_id_for_contractor( $contractor_id ) {
		if ( isset( self::$instances[$contractor_id] ) ) {
			return self::$instances[$contractor_id]->user_id;
		} else {
			$user_id = get_post_meta( $contractor_id, self::$meta_keys['user_id'], TRUE );
			return $user_id;
		}
	}

	/**
	 * Create a new contractor
	 * Note: Returns wp_error if wp_insert_post fails
	 */
	public static function new_contractor( $user_id, $title = '', $data = array() ) {
		if ( empty( $title ) && $user_id ) {
			$user = get_userdata( $user_id );
			if ( !is_a( $user, 'WP_User' ) ) { // prevent an contractor being created without a proper user_id associated.
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
				'post_title' => self::__( 'Contractor' ).' '.$_SERVER['REMOTE_ADDR'],
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
	 * Get the contractor ID cache for the user
	 */
	private static function get_id_cache( $id ) {
		return (int)wp_cache_get(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * Set the contractor ID cache for the user
	 */
	private static function set_id_cache( $id, $contractor_id ) {
		wp_cache_set(self::CACHE_PREFIX.'_id_'.$id, (int)$contractor_id, self::CACHE_GROUP);
	}

	/**
	 * Delete the contractor ID cache for the user
	 */
	private static function clear_id_cache( $id ) {
		wp_cache_delete(self::CACHE_PREFIX.'_id_'.$id, self::CACHE_GROUP);
	}

	/**
	 * If the current query is for the contractor post type
	 */
	public static function is_contractor_query() {
		$post_type = get_query_var( 'post_type' );
		if ( $post_type == self::POST_TYPE ) {
			return TRUE;
		}
		return FALSE;
	}
	
	/**
	 * Looup contractor by user_id
	 */
	public static function get_contractors_by_user( $user_id ) {
		$contractors = self::lookup_by_meta( self::POST_TYPE, array( self::$meta_keys['user_id'] => $user_id ) );
		return $contractors;
	}

	/**
	 * Get all contractor posts
	 */
	public static function get_contractors() {
		$args = array(
				'post_type' => self::POST_TYPE,
				'post_status' => 'publish',
				'posts_per_page' => -1,
				'fields' => 'ids'
			);
		$contractors = new WP_Query( $args );
		return $contractors->posts;
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
		 $years_of_experience = self::get_field($post->ID, 'years_of_experience');
		 ?>
         <label for="<?php echo self::$meta_keys['years_of_experience'] ?>"><?php _e('Years of Experience'); ?></label><br />
         <select name="<?php echo self::$meta_keys['years_of_experience'] ?>">
            <option value="" <?php echo ($years_of_experience == '') ? 'selected="selected"' : ''; ?>>select your years of experience</option>
            <option value="1" <?php echo ($years_of_experience == '1') ? 'selected="selected"' : ''; ?>>1 year</option>
            <option value="2" <?php echo ($years_of_experience == '2') ? 'selected="selected"' : ''; ?>>2 years</option>
            <option value="3" <?php echo ($years_of_experience == '3') ? 'selected="selected"' : ''; ?>>3 years</option>
            <option value="4" <?php echo ($years_of_experience == '4') ? 'selected="selected"' : ''; ?>>4 years</option>
            <option value="5" <?php echo ($years_of_experience == '5') ? 'selected="selected"' : ''; ?>>5 years</option>
            <option value="6" <?php echo ($years_of_experience == '6') ? 'selected="selected"' : ''; ?>>6 years</option>
            <option value="7" <?php echo ($years_of_experience == '7') ? 'selected="selected"' : ''; ?>>7 years</option>
            <option value="8" <?php echo ($years_of_experience == '8') ? 'selected="selected"' : ''; ?>>8 years</option>
            <option value="9" <?php echo ($years_of_experience == '9') ? 'selected="selected"' : ''; ?>>9 years</option>
            <option value="10-15" <?php echo ($years_of_experience == '10-15') ? 'selected="selected"' : ''; ?>>10-15 years</option>
            <option value="16-20" <?php echo ($years_of_experience == '16-20') ? 'selected="selected"' : ''; ?>>16-20 years</option>
            <option value="20-30" <?php echo ($years_of_experience == '20-30') ? 'selected="selected"' : ''; ?>>20-30 years</option>
            <option value="30+" <?php echo ($years_of_experience == '30+') ? 'selected="selected"' : ''; ?>>30+ years</option>
         </select>
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['contractor_license'] ?>"><?php _e('General Contractor license & state'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['contractor_license'] ?>" id="<?php echo self::$meta_keys['contractor_license'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'contractor_license'))); ?>">
        </p>
        <p>
            <label for="<?php echo self::$meta_keys['insurance_account'] ?>"><?php _e('Insurance Account & Number'); ?></label><br />
            <input class="large-text" type="text" name="<?php echo self::$meta_keys['insurance_account'] ?>" id="<?php echo self::$meta_keys['insurance_account'] ?>" value="<?php echo stripslashes(esc_attr(self::get_field($post->ID, 'insurance_account'))); ?>">
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
	
		// only continue if it's an contractor post
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
		if ( isset( $_POST[self::$meta_keys['contractor_license']] ) ) {
			update_post_meta($post_id, self::$meta_keys['contractor_license'], stripslashes($_POST[self::$meta_keys['contractor_license']]) );
		}
		if ( isset( $_POST[self::$meta_keys['insurance_account']] ) ) {
			update_post_meta($post_id, self::$meta_keys['insurance_account'], stripslashes($_POST[self::$meta_keys['insurance_account']]) );
		}
		if ( isset( $_POST[self::$meta_keys['years_of_experience']] ) ) {
			update_post_meta($post_id, self::$meta_keys['years_of_experience'], stripslashes($_POST[self::$meta_keys['years_of_experience']]) );
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
		if ( isset( $_POST[self::$meta_keys['contractor_license']] ) ) {
			update_post_meta($post_id, self::$meta_keys['contractor_license'], stripslashes($_POST[self::$meta_keys['contractor_license']]) );
		}
		if ( isset( $_POST[self::$meta_keys['insurance_account']] ) ) {
			update_post_meta($post_id, self::$meta_keys['insurance_account'], stripslashes($_POST[self::$meta_keys['insurance_account']]) );
		}
		if ( isset( $_POST[self::$meta_keys['years_of_experience']] ) ) {
			update_post_meta($post_id, self::$meta_keys['years_of_experience'], stripslashes($_POST[self::$meta_keys['years_of_experience']]) );
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
			$allowed_categories = fv_get_contractor_membership_addon_categories($post_id);
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
			//also save data as array in order sent to preserve  the primary
			update_post_meta($post_id, self::$meta_keys['category_data'], $selected_cats );
		}
		/*
		if ( isset( $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY] ) ) {
			wp_set_post_terms( $post_id, $_POST[SF_Taxonomies::JOB_SKILL_TAXONOMY], SF_Taxonomies::JOB_SKILL_TAXONOMY );
		}
		*/
		
	}
	
	public static function load_attachments($post_id) {
		//Load photos - attachments to this post
		$featured_thumb_id = get_post_meta( $post_id, '_thumbnail_id', true);	
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
